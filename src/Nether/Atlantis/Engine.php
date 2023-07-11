<?php

namespace Nether\Atlantis;

use Monolog;
use Nether;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Database;
use Nether\Ki;
use Nether\Storage;
use Nether\Surface;
use Nether\User;

use Nether\Common\Datastore;

class Engine {
/*//
Provides a core application instance and API to bring all the framework
components together to drive the app. It will manage instances for cases
such as Theme Engines and Routers where it only makes sense to have one per
application instance.
//*/

	public Datastore
	$Config;

	public Datastore
	$Library;

	public Avenue\Router
	$Router;

	public Storage\Manager
	$Storage;

	public Database\Manager
	$Database;

	public Surface\Engine
	$Surface;

	public ?User\EntitySession
	$User;

	public Util\LogManager
	$Log;

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
	Ki\CallbackPackage;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ProjectRoot, ?Datastore $Conf=NULL) {

		// prepare some defaults.

		$this->ProjectTime = microtime(TRUE);
		$this->ProjectRoot = $ProjectRoot;
		$this->ProjectEnv = 'dev';
		$this->Config = new Datastore;
		$this->Library = new Datastore;

		// load in configuration.

		$this
		->InitLogging()
		->DetermineEnvironment()
		->LoadDefaultConfig()
		->LoadProjectConfig()
		->LoadEnvironmentConfig()
		->SetupEnvironment();

		if($Conf !== NULL)
		$this->Config->MergeRight($Conf->GetData());

		// begin setting things up.

		if($this->Config->IsTrueEnough(Atlantis\Library::ConfProjectDefineConsts))
		$this->DefineProjectConsts();

		$this
		->LoadRequiredLibraries()
		->LoadAdditionalLibraries()
		->UpdateLogging();

		$this->Library->Each(function(Nether\Common\Library $Inst){

			$this
			->Queue('Atlantis.Prepare', $Inst->OnPrepare(...))
			->Queue('Atlantis.Ready', $Inst->OnReady(...));

			return;
		});

		// spool up our instances.

		$this->Router = new Avenue\Router($this->Config);
		$this->Surface = new Surface\Engine($this->Config);
		$this->Storage = new Storage\Manager($this->Config);
		$this->Database = new Database\Manager($this->Config);

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

		$this->Router->Run(new Nether\Avenue\Struct\ExtraData([
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

	#[Common\Meta\Deprecated('2023-07-10', 'use FromConfRoot')]
	public function
	GetConfigRoot(?string $File=NULL):
	string {

		return $this->FromConfRoot($File);
	}

	#[Common\Meta\Deprecated('2023-07-10', 'use FromEnvConf')]
	public function
	GetEnvConfigRoot(?string $File=NULL):
	string {

		return $this->FromEnvConf($File);
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

	#[Common\Meta\Date('2023-07-10')]
	public function
	FromEnvConf(?string $File=NULL):
	string {

		$File ??= '';
		$Path = sprintf('env/%s/%s', $this->GetProjectEnv(), $File);

		return $this->FromConfRoot(rtrim($Path, '/'));
	}

	#[Common\Meta\Date('2023-07-10')]
	public function
	FromConfRoot(?string $File=NULL):
	string {

		$File ??= '';
		$Path = sprintf('conf/%s', $File);

		return $this->FromProjectRoot(rtrim($Path, '/'));
	}

	#[Common\Meta\Date('2023-07-10')]
	public function
	FromProjectRoot(?string $File=NULL):
	string {

		$File ??= '';
		$Path = NULL;

		////////

		if($File && Common\Filesystem\Util::IsAbsolutePath($File))
		return $File;

		////////

		$Path = rtrim(sprintf(
			'%s/%s',
			$this->ProjectRoot, Util::Repath($File)
		));

		return $Path;
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
	InitLogging():
	static {

		$this->Log = new Util\LogManager($this->ProjectRoot);
		$this->Log->Init();

		return $this;
	}

	protected function
	UpdateLogging():
	static {

		$this->Log->Update(
			$this->Config[Atlantis\Library::ConfLogFormat]
		);

		return $this;
	}

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

	protected function
	SetupEnvironment():
	static {

		$ErrLogPath = $this->Config[Library::ConfErrorLogPath];
		$ErrDisplay = $this->Config[Library::ConfErrorDisplay];

		////////

		// default behaviour is to set the error log to be within the app
		// logs folder. it can also be forced to a specific path if given
		// one.

		ini_set('error_log', match(TRUE) {
			$ErrLogPath === NULL
			=> $this->FromProjectRoot('logs/error.log'),

			is_string($ErrLogPath)
			=> $this->FromProjectRoot($ErrLogPath),

			default
			=> NULL
		});

		////////

		// default behaviour is to show errors on dev but not on prod
		// environments. it can also be forced on or off in the config.

		ini_set('display_errors', match(TRUE) {
			$ErrDisplay === NULL
			=> $this->IsDev(),

			default
			=> (bool)$ErrDisplay
		});

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
			Surface\Library::ConfThemeRoot,
			"{$this->ProjectRoot}/www/themes"
		)
		->Define(
			User\Library::ConfAppleKeyFilePath,
			"{$this->ProjectRoot}/conf/env/{$this->ProjectEnv}/keys/apple-authkey.p8"
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
		(function(string $__FILENAME, Nether\Common\Datastore $Config, Nether\Atlantis\Engine $App){
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
		(function(string $__FILENAME, Nether\Common\Datastore $Config, Nether\Atlantis\Engine $App){
			require($__FILENAME);
			return;
		})($File, $this->Config, $this);

		return $this;
	}

	protected function
	LoadRequiredLibraries():
	static {

		($this->Library)
		->Shove('Common', new Nether\Common\Library(Config: $this->Config))
		->Shove('Avenue', new Nether\Avenue\Library(Config: $this->Config))
		->Shove('Surface', new Nether\Surface\Library(Config: $this->Config))
		->Shove('Storage', new Nether\Storage\Library(Config: $this->Config))
		->Shove('Database', new Nether\Database\Library(Config: $this->Config))
		->Shove('User', new Nether\User\Library(Config: $this->Config))
		->Shove('Email', new Nether\Email\Library(Config: $this->Config))
		->Shove('Atlantis', new Nether\Atlantis\Library(Config: $this->Config));

		return $this;
	}

	protected function
	LoadAdditionalLibraries():
	static {

		$Classes = $this->Config[Library::ConfLibraries];
		$Class = NULL;

		if(is_iterable($Classes))
		foreach($Classes as $Class) {
			if(!class_exists($Class))
			throw new \Exception("library {$Class} not found");

			if(!is_subclass_of($Class, 'Nether\\Common\\Library'))
			throw new \Exception("library {$Class} is not valid");

			$this->Library->Shove($Class, new $Class(Config: $this->Config, App: $this));
		}

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

}