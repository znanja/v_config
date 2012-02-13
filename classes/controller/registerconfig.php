<?php defined('SYSPATH') or die("No direct script access.");
/**
 * Read the read the configuration files from the filesystem,
 * and load them into the database
 *
 * @package Velsoft/Config
 * @category Resources
 * @author Velsoft Training Materials, Inc.
 */
class Controller_RegisterConfig extends Controller
{
	public function before()
	{
		if( ! Kohana::$is_cli )
			throw new KohanaException("This script must be run from the CLI");

		// Close Kohana's output buffer so that output is displayed in realtime
		ob_end_flush();

		$config = Kohana::$config->load('config');
		
		$userstr = '';
		if($config->username !== NULL)
		{
			$userstr = "$config->username:$config->password@";
		}

		// Normally we would use v_mongo, but we don't have that loaded yet
		$this->_mongo = new Mongo("mongodb://{$userstr}{$config->host}");
		$this->_db = $this->_mongo->{$config->db};
	}

	public function action_index()
	{
		$config = array();

		$drop = in_array('--drop-existing', $_SERVER['argv']);

		if($drop)
		{
			echo "Droping existing data - are your sure? y/n: ";

			$yes = strtolower(trim(fgets(STDIN))) === 'y';
			if ($yes)
			{
				echo "Removing existing data\n";
				$this->_db->drop();
				echo "Done removing data\n";
			}
		}

		foreach (Kohana::list_files('config') as $_name => $_file)
		{
			$name = preg_replace("/config\/(.*?)\.php/", "$1", $_name);
			echo "Loading $name...\n";

			$__merged_config = array();
			
			/**
			 * Merge all the configurations together. Load the configuration in
			 * reverse, so we have the correct order from the cascading file
			 * system
			 */
			$__files = array_reverse(Kohana::find_file('config', $name, NULL, TRUE));
			foreach($__files as $__merge)
			{
				echo "Merging...\n";
				try {
					// If there is actual PHP in our config, we might mess up our scoping
					$cscope = function() use($__merge) {return include $__merge;};
					$__config = $cscope();
				} catch (Exception $ex) {
					echo "ERROR: Looks like I got an error: {$ex->getMessage()}\n";
					$__config = array();
				}

				$__merged_config = array_merge($__config, $__merged_config);
			}
			
			$this->_load($__merged_config, $name);

			echo "Done loading $name\n";
		}

		echo "All done! Please review for errors\n";
	}

	protected function _load($config, $group)
	{
		$collection = $this->_db->selectCollection($group);

		try {
			array_walk_recursive($config, function($value, $key){
				if (is_object($value))
					$config[$key] = serialize($value);
			});

			$collection->insert($config, array('fsync'=>TRUE));	
		} catch(Exception $ex)
		{
			echo "ERROR: Couldn't insert $group: {$ex->getMessage()}\n";
		}
	}
}
