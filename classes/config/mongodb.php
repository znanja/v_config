<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana Mongo Configuration Drivers
 *
 * A configuration drive for Kohana that allows configuration options to
 * be stored in a MongoDB database, rather than a flat file. The advantages
 * being configuration values can be access by a variety of platforms
 * besides Kohana, and can be edited on the fly (to update shared databases,
 * master/slave DBs or make general changes via a config)
 *
 * @author znanja, inc
 * @category Configuration
 * @package znanja/Config
 * @see Kohana::$config
 */
class Config_MongoDB extends Kohana_Config_Reader
{
	/**
	 * @var  string  Configuration group name
	 */
	protected $_configuration_group;

	/**
	 * @var   Mongo_Database The db to use
	 */
	protected $_mongo = NULL;

	public function __construct()
	{
		/**
		 *  We load our (likely file-based) configuration,
		 *  from our previously attached driver (this one
		 *  hasn't been completely attached yet)
		 */
		$config = Kohana::$config->load('config');

		$userstr = '';
		if($config->username !== NULL)
		{
			$userstr = "$config->username:$config->password@";
		}

		// Normally we would use v_mongo, but we don't have that loaded yet
		$this->_mongo = new Mongo("mongodb://{$userstr}{$config->host}");
		$this->_db = $this->_mongo->{$config->db};

		parent::__construct();
	}

	
	/**
	 * Load and merge all of the configuration files in this group.
	 *		
	 *     $config->load($name);
	 *
	 * @param   string  configuration group name
	 * @param   array   configuration array
	 * @return  $this   clone of the current object
	 * @uses    Kohana::load
	 */
	public function load($group, array $config = NULL)
	{
		(Kohana::$profiling === TRUE) ? $token = Profiler::start("Mongo Config", __FUNCTION__):FALSE;

		$file = $this->_db->selectCollection($group);

		$config = array();

		try
		{
			$documents = $file->find();

			while($documents->hasNext())
			{
				$__doc = $documents->getNext();
				$config = array_merge($config, $__doc);
			}
		}catch(MongoCursorException $mex){}
		
		unset($config['_id']);

		isset($token)?Profiler::stop($token):FALSE;
		return parent::load($group, $config);
	}
}
