<?php

namespace Nether\Atlantis;
use Nether;

class Engine {
/*//
provides the core application instance and api for bringing all the components
to drive the app together. it will manage instances for cases where like the
theme engine and routers it makes sense where you would only ever have one for
the entire application scope.
//*/

	public Nether\Object\Datastore
	$Config;

	public Nether\Avenue\Router
	$Router;

	public Nether\Surface\Engine
	$Surface;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected string
	$ProjectEnv;

	protected string
	$ProjectRoot;

	protected float
	$ProjectTime;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ProjectRoot, ?Nether\Object\Datastore $Conf=NULL) {

		// prepare some defaults.

		$this->ProjectTime = microtime(TRUE);
		$this->ProjectRoot = $ProjectRoot;
		$this->ProjectEnv = 'dev';
		$this->Config = new Nether\Object\Datastore;

		// load in configuration.

		$this
		->DetermineEnvironment()
		->LoadDefaultConfig()
		->LoadProjectConfig()
		->LoadEnvironmentConfig();

		if($Conf !== NULL)
		$this->Config->MergeRight($Conf->GetData());

		// begin setting things up.

		if($this->Config->IsTrueEnough(Library::ConfProjectDefineConsts))
		$this->DefineProjectConsts();

		$this
		->LoadRequiredLibraries()
		->LoadAdditionalLibraries();

		// spool up our instances.

		$this->Router = new Nether\Avenue\Router($this->Config);
		$this->Surface = new Nether\Surface\Engine($this->Config);

		return;
	}

	public function
	__Invoke(...$Argv):
	mixed {

		return $this->Run(...$Argv);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Run():
	static {

		$this->Router->Run(new Nether\Object\Datastore([
			'App'=> $this
		]));

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsEnv(string $Env):
	bool {
	/*//
	return if the ProjectEnv matches what was asked for.
	//*/

		return ($this->ProjectEnv === $Env);
	}

	public function
	IsDev():
	bool {
	/*//
	return if this environment is a development one.
	//*/

		if(str_starts_with($this->ProjectEnv, 'dev-'))
		return TRUE;

		return $this->IsEnv('dev');
	}

	public function
	IsProd():
	bool {
	/*//
	return if this environment is a production one.
	//*/

		if(str_starts_with($this->ProjectEnv, 'prod-'))
		return TRUE;

		return $this->IsEnv('prod');
	}

	public function
	GetProjectEnv():
	string {

		return $this->ProjectEnv;
	}

	public function
	GetProjectEnvType():
	string {

		//if($this->IsDev())
		//return 'dev';

		//if($this->IsProd())
		//return 'prod';

		if(str_contains($this->ProjectEnv, '-'))
		return explode('-', $this->ProjectEnv, 2)[0];

		return 'unknown';
	}

	public function
	GetProjectRoot():
	string {

		return $this->ProjectRoot;
	}

	public function
	GetProjectTime():
	float {

		return $this->ProjectTime;
	}

	public function
	GetConfigRoot(?string $File=NULL):
	string {

		return sprintf(
			'%s/conf%s',
			$this->ProjectRoot,
			($File ? "/{$File}" : '')
		);
	}

	public function
	GetEnvConfigRoot(?string $File=NULL):
	string {

		return sprintf(
			'%s/conf/env/%s%s',
			$this->ProjectRoot,
			$this->ProjectEnv,
			($File ? "/{$File}" : '')
		);
	}

	public function
	GetWebRoot():
	string {

		$WebRoot = sprintf(
			'%s/%s',
			$this->ProjectRoot,
			$this->Config['Project.WebRoot']
		);

		return $WebRoot;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	DetermineEnvironment():
	static {

		$EnvFile = sprintf('%s/env.lock', $this->ProjectRoot);
		$EnvData = NULL;

		////////

		if(!file_exists($EnvFile))
		return $this;

		if(!is_readable($EnvFile))
		return $this;

		////////

		$EnvData = Nether\Avenue\Util::MakePathableKey(
			trim(file_get_contents($EnvFile))
		);

		if(strlen($EnvData))
		$this->ProjectEnv = $EnvData;

		////////

		return $this;
	}

	public function
	DefineProjectConsts():
	static {

		if(!defined('ProjectRoot'))
		define('ProjectRoot', $this->ProjectRoot);

		if(!defined('ProjectTime'))
		define('ProjectTime', $this->ProjectTime);

		if(!defined('ProjectEnv'))
		define('ProjectEnv', $this->ProjectEnv);

		return $this;
	}

	protected function
	LoadDefaultConfig():
	static {

		($this->Config)
		->Define('Project.WebRoot', 'www')
		->Define('Project.WebServerType', NULL);

		return $this;
	}

	protected function
	LoadProjectConfig():
	static {

		$Files = [
			sprintf('%s/conf/config.php', $this->ProjectRoot),
			sprintf('%s/conf/local.php', $this->ProjectRoot)
		];

		foreach($Files as $File)
		if(is_readable($File))
		(function(string $__FILENAME, Nether\Object\Datastore $Config){
			require($__FILENAME);
			return;
		})($File, $this->Config);

		return $this;
	}

	protected function
	LoadEnvironmentConfig():
	static {

		$File = sprintf(
			'%s/conf/env/%s/config.php',
			$this->ProjectRoot,
			$this->ProjectEnv
		);

		if(is_readable($File))
		(function(string $__FILENAME, Nether\Object\Datastore $Config){
			require($__FILENAME);
			return;
		})($File, $this->Config);

		return $this;
	}

	protected function
	LoadRequiredLibraries():
	static {

		Nether\Avenue\Library::Init($this->Config);
		Nether\Surface\Library::Init($this->Config);

		return $this;
	}

	protected function
	LoadAdditionalLibraries():
	static {

		$Libs = $this->Config[Library::ConfProjectInitWithConfig] ?? [];
		$Class = NULL;

		foreach($Libs as $Class) {

			if(!class_exists($Class))
			continue;

			// @todo - decide which package to insert the interface
			// into. atm none of them really make sense. object is
			// required by nearly everything so maybe there.

			// if(!is_subclass_of($Class, LibraryInitWithConfig::class))
			// continue;

			// for now we check the duck's undercarriage.

			if(method_exists($Class, 'Init'))
			($Class)::Init($this->Config);
		}

		return $this;
	}

}