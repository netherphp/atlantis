<?php

namespace Nether\Atlantis;
use Nether;

class Engine {
/*//
Provides a core application instance and API to bring all the framework
components together to drive the app. It will manage instances for cases
such as Theme Engines and Routers where it only makes sense to have one per
application instance.
//*/

	public Nether\Object\Datastore
	$Config;

	public Nether\Avenue\Router
	$Router;

	public Nether\Surface\Engine
	$Surface;

	public ?Nether\User\EntitySession
	$User;

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

	use
	Nether\Ki\CallbackPackage;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ProjectRoot, ?Nether\Object\Datastore $Conf=NULL) {
		session_start();

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

		$Data = [
			'App'    => $this,
			'Config' => $this->Config,
			'Path'   => $this->ProjectRoot,
			'Env'    => $this->ProjectEnv
		];

		$this->Flow('Atlantis.Prepare', $Data, FALSE);
		$this->Flow('Atlantis.Ready', $Data, FALSE);
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
	Return if this environment is indeed the one that was asked about.
	//*/

		return ($this->ProjectEnv === $Env);
	}

	public function
	IsDev():
	bool {
	/*//
	Return if this environment smells like a development one. This includes
	both the literal 'dev' env and any env prefixed with 'dev-'.
	//*/

		if(str_starts_with($this->ProjectEnv, 'dev-'))
		return TRUE;

		return $this->IsEnv('dev');
	}

	public function
	IsProd():
	bool {
	/*//
	Return if this environment smells like a production one. This includes
	both the literal 'prod' env and any env prefixed with 'prod-'.
	//*/

		if(str_starts_with($this->ProjectEnv, 'prod-'))
		return TRUE;

		return $this->IsEnv('prod');
	}

	public function
	GetProjectEnv():
	string {
	/*//
	Returns whatever the currently active environment is named.
	//*/

		return $this->ProjectEnv;
	}

	public function
	GetProjectEnvType():
	string {
	/*//
	Returns whatever the current environment is classified as. Environments
	are classified by whatever their prefix is. 'dev-local' for example will
	return 'dev' while 'prod-websrv9' will return 'prod'. Without a prefix
	and not one of the literals, it will return 'unknown'.
	//*/

		if($this->IsDev())
		return 'dev';

		if($this->IsProd())
		return 'prod';

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

	public function
	FromProjectRoot(string $Path):
	string {

		$Output = sprintf(
			'%s%s%s',
			$this->ProjectRoot,
			DIRECTORY_SEPARATOR,
			Util::Repath($Path)
		);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetLocalData(string $Key, mixed $Val):
	static {
	/*//
	adds data to the session storage.
	//*/

		if(isset($_SESSION))
		$_SESSION[$Key] = $Val;

		return $this;
	}

	public function
	UnsetLocalData(string $Key):
	static {
	/*//
	removes data from the session storage.
	//*/

		if(isset($_SESSION[$Key]))
		unset($_SESSION[$Key]);

		return $this;
	}

	public function
	GetLocalData(string $Key):
	mixed {
	/*//
	reads and returns data from the session storage.
	//*/

		if(isset($_SESSION[$Key]))
		return $_SESSION[$Key];

		return NULL;
	}

	public function
	YoinkLocalData(string $Key):
	mixed {
	/*//
	read, drop, and then return the data from the session storage.
	//*/

		$Output = $this->GetLocalData($Key);
		$this->UnsetLocalData($Key);

		return $Output;
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
		->Define('Project.WebServerType', NULL)
		->Define(
			Nether\Surface\Library::ConfThemeRoot,
			"{$this->ProjectRoot}/www/themes"
		);

		return $this;
	}

	protected function
	LoadProjectConfig():
	static {

		$Files = [
			sprintf('%s/conf/config.php', $this->ProjectRoot),
			sprintf('%s/conf/local.php', $this->ProjectRoot)
		];

		$File = NULL;

		foreach($Files as $File)
		if(is_readable($File))
		(function(string $__FILENAME, Nether\Object\Datastore $Config, Nether\Atlantis\Engine $App){
			require($__FILENAME);
			return;
		})($File, $this->Config, $this);

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
		(function(string $__FILENAME, Nether\Object\Datastore $Config, Nether\Atlantis\Engine $App){
			require($__FILENAME);
			return;
		})($File, $this->Config, $this);

		return $this;
	}

	protected function
	LoadRequiredLibraries():
	static {

		Nether\Common\Library::Init(Config: $this->Config);
		Nether\Atlantis\Library::Init(Config: $this->Config);
		Nether\Avenue\Library::Init(Config: $this->Config);
		Nether\Surface\Library::Init(Config: $this->Config);

		($this)
		->Queue('Atlantis.Prepare', Nether\Database\Library::Init(...))
		->Queue('Atlantis.Prepare', Nether\Email\Library::Init(...))
		->Queue('Atlantis.Prepare', Nether\User\Library::Init(...))
		->Queue('Atlantis.Prepare', Nether\Atlantis\Library::Prepare(...));

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

			if(is_subclass_of($Class, Nether\Common\Library::class)) {
				($Class)::Init(Config: $this->Config);
			}

			// @todo - decide which package to insert the interface
			// into. atm none of them really make sense. object is
			// required by nearly everything so maybe there.

			// if(!is_subclass_of($Class, LibraryInitWithConfig::class))
			// continue;

			// for now we check the duck's undercarriage.

			if(method_exists($Class, 'Init'))
			($Class)::Init(Config: $this->Config);
		}

		return $this;
	}

}