<?php

/*//////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

Constants Defined:

	ProjectTime - float: time project began loading.
	ProjectRoot - string: path to the project root.
	ProjectEnv - string: name of the env from env.lock (default 'dev')
	ProjectDev - bool: if project is in developer run mode.
	ProjectProd - bool: if project is in production run mode.

Config Loaded:

	conf/config.json
	conf/env/{ProjectEnv}/config.json

Returns:

	Project-wide configuration data object.

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////*/

use
Nether\Avenue\Struct\LibraryInitWithConfig,
Nether\Object\Datastore;

return (function(){

	// bring in the autoloader.

	define('ProjectTime', microtime(TRUE));
	define('ProjectRoot', dirname(__FILE__, 2));
	require(sprintf('%s/vendor/autoload.php', ProjectRoot));

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// bring in site config.

	$Config = new Datastore;
	$Config->Read(sprintf('%s/conf/config.json', ProjectRoot));

	// determine environment config.

	(function() use($Config) {

		$Env = 'dev';
		$EnvFile = sprintf('%s/env.lock', ProjectRoot);

		if(file_exists($EnvFile) && is_readable($EnvFile)) {
			$EnvData = trim(file_get_contents($EnvFile));
			$EnvConf = sprintf(
				'%s/conf/env/%s/config.json',
				ProjectRoot,
				$EnvData
			);

			if(file_exists($EnvConf) && is_readable($EnvConf)) {
				$Env = $EnvData;
				$Config->Read($EnvConf, TRUE);
			}
		}

		define('ProjectEnv', $Env);
		define('ProjectDev', ($Env === 'dev'));
		define('ProjectProd', ($Env === 'prod'));

		return;
	})();

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// initialise libraries.

	(function() use($Config) {

		$Libs = $Config['Project.InitWithConfig'];
		$Lib = NULL;
		$Class = NULL;

		foreach($Libs as $Lib) {
			$Class = "{$Lib}";

			if(!class_exists($Class))
			continue;

			//if(!is_subclass_of($Class, LibraryInitWithConfig::class))
			//continue;

			($Class)::Init($Config);
		}

		return;
	})();

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	return $Config;
})();
