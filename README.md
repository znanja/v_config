# Kohana Configuration Driver for MongoDB datastore

## Requirements

v_config has very light requirements:

* Kohana 3.0
** While this may work for versions of Kohana beyond 3.0, it has not been tested
* Mongo server or servers available and accessable
* MongoDB PECL installed (http://ca2.php.net/manual/en/mongo.installation.php)

## Loading your configuration

To load your configuration, run the ``load_configs.sh`` file in the ``resources/`` folder. This file will read the configuration files using Kohana's ``list_files`` method, and load the configuration from those files into the ``db`` specified in ``config/config.php``. The file at ``config/config.php`` is the only file-based configuration required beyond this point.

## Using v_config

To use ``v_config``, add the following in your bootstrap

	try {
		Kohana::$config->attach(New Config_MongoDB);
	} catch (MongoConnectionException $e) {
		Kohana::$log->add(Kohana::ERROR, "Failed to initlaize Mongo Configuration driver");
	}

below your module initialization.