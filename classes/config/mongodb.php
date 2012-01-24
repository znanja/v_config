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
 * @author Velsoft Training Materials
 * @category Configuration
 * @package Velsoft/Config
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
		$this->_mongo = Mongo_Database::instance();

		parent::__construct();
	}

}
