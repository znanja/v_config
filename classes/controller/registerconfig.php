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
			try
			{
				$__config = include $_file;
			}catch(Exception $ex)
			{
				echo "ERROR: Looks like I got an error: {$ex->getMessage()}\n";
				$__config = array();
			}
			$name = preg_replace("/config\/(.*?)\.php/", "$1", $_name);

			echo "Loading $name...\n";
			$this->_load($__config, $name);
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