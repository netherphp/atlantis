<?php ##########################################################################
################################################################################

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Database;
use Nether\Email;
use Nether\Ki;
use Nether\Storage;
use Nether\Surface;
use Nether\User;

################################################################################
################################################################################

class Engine {
/*//
Provides a core application instance and API to bring all the framework
components together to drive the app. It will manage instances for cases
such as Theme Engines and Routers where it only makes sense to have one per
application instance.
//*/

	public Common\Datastore
	$Config;

	public Common\Datastore
	$Library;

	public Avenue\Router
	$Router;

	public Storage\Manager
	$Storage;

	public Database\Manager
	$Database;

	public Plugin\Manager
	$Plugins;

	public Surface\Engine
	$Surface;

	public ?User\EntitySession
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
	Ki\CallbackPackage;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ProjectRoot, ?Common\Datastore $Conf=NULL) {

		// prepare some defaults.

		$this->ProjectTime = microtime(TRUE);
		$this->ProjectRoot = $ProjectRoot;
		$this->ProjectEnv = 'dev';
		$this->Config = new Common\Datastore;
		$this->Library = new Common\Datastore;
		$this->Plugins = new Plugin\Manager($this);

		// load in configuration.

		$this
		->InitLogging()
		->DetermineEnvironment()
		->LoadDefaultConfig()
		->LoadProjectConfig()
		->LoadEnvironmentConfig()
		->LoadProjectJSON()
		->SetupEnvironment();

		if($Conf !== NULL)
		$this->Config->MergeRight($Conf->GetData());

		// begin setting things up.

		if($this->Config->IsTrueEnough(Atlantis\Key::ConfProjectDefineConsts))
		$this->DefineProjectConsts();

		$this
		->LoadRequiredLibraries()
		->LoadAdditionalLibraries();

		$this->Library->Each(function(Common\Library $Inst){

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

		$this->Flow(
			'Atlantis.Prepare',
			[
				'App'    => $this,
				'Config' => $this->Config,
				'Path'   => $this->ProjectRoot,
				'Env'    => $this->ProjectEnv
			],
			FALSE
		);

		$this->Flow('Atlantis.Config', [ 'App'=> $this ], FALSE);
		$this->Flow('Atlantis.Ready', [ 'App'=> $this ], FALSE);
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

		$IgnoreUA = Library::Get(Key::ConfAccessIgnoreAgentHard);
		$InputUA = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : NULL;
		$Meh = NULL;
		$Err = NULL;

		////////

		if($IgnoreUA && $InputUA) {
			$Meh = preg_match(
				sprintf('#\b(?:%s)\b#msi', $IgnoreUA),
				$InputUA
			);

			if($Meh) {
				if($this->Router->Response->HTTP)
				header('HTTP/1.1 404 Not Found');

				return $this;
			}
		}

		////////

		try {
			$this->Router->Run(new Avenue\Struct\ExtraData([
				'App'=> $this
			]));
		}

		catch(Error\Quit $Err) {
			$this->Router->Render();
		}

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
	SetProjectEnv(string $Env):
	static {

		$this->ProjectEnv = $Env;

		return $this;
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

	#[Common\Meta\DateAdded('2023-07-10')]
	public function
	FromConfRoot(?string $File=NULL):
	string {

		$File ??= '';
		$Path = sprintf('conf/%s', $File);

		return Common\Filesystem\Util::Repath(
			$this->FromProjectRoot(rtrim($Path, '/'))
		);
	}

	#[Common\Meta\DateAdded('2023-07-10')]
	public function
	FromConfEnv(?string $File=NULL, ?string $Env=NULL):
	string {

		$File ??= '';
		$Env ??= $this->GetProjectEnv();
		$Path = sprintf('env/%s/%s', $Env, $File);

		return Common\Filesystem\Util::Repath(
			$this->FromConfRoot(rtrim($Path, '/'))
		);
	}

	#[Common\Meta\DateAdded('2023-07-10')]
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

		return Common\Filesystem\Util::Repath($Path);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasUser():
	bool {

		return isset($this->User);
	}

	public function
	HasLocalData(string $Key):
	bool {

		if(!isset($_SESSION))
		return FALSE;

		if(!isset($_SESSION[$Key]))
		return FALSE;

		return TRUE;
	}

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


		return $this;
	}

	protected function
	DetermineEnvironment():
	static {

		$EnvFile = sprintf('%s/env.lock', $this->ProjectRoot);
		$EnvData = NULL;

		////////

		if(isset($_ENV['ATLANTIS.ENV'])) {
			$EnvData = trim($_ENV['ATLANTIS.ENV']);
		}

		else {
			if(!file_exists($EnvFile))
			return $this;

			if(!is_readable($EnvFile))
			return $this;

			$EnvData = Avenue\Util::MakePathableKey(
				trim(file_get_contents($EnvFile))
			);
		}

		////////

		if(strlen($EnvData))
		$this->ProjectEnv = $EnvData;

		////////

		return $this;
	}

	protected function
	SetupEnvironment():
	static {

		$ErrLogPath = $this->Config[Key::ConfErrorLogPath];
		$ErrDisplay = $this->Config[Key::ConfErrorDisplay];

		////////

		// default behaviour is to set the error log to be within the app
		// logs folder. it can also be forced to a specific path if given
		// one.

		/*
		ini_set('error_log', match(TRUE) {
			$ErrLogPath === NULL
			=> $this->FromProjectRoot('logs/error.log'),

			is_string($ErrLogPath)
			=> $this->FromProjectRoot($ErrLogPath),

			default
			=> NULL
		});
		*/

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
	LoadProjectJSON():
	static {

		$JSON = Struct\ProjectJSON::FromApp($this);
		$DBC = new Common\Datastore;

		if(is_iterable($this->Config[Database\Library::ConfConnections]))
		$DBC->MergeRight($this->Config[Database\Library::ConfConnections]);

		////////

		$JSON->Each(function(Struct\ProjectJSON $Project) use($DBC) {

			if($Project->DB->HasAnything())
			$DBC->MergeRight($Project->DB->GetConnections());

			return;
		});

		////////

		$this->Config[Database\Library::ConfConnections] = $DBC->GetData();

		////////

		return $this;
	}

	protected function
	LoadProjectConfig():
	static {

		// load config script files.

		$Files = [
			sprintf('%s/conf/config.php', $this->ProjectRoot),
			sprintf('%s/conf/local.php', $this->ProjectRoot)
		];

		$File = NULL;

		foreach($Files as $File)
		if(is_readable($File))
		(function(string $__FILENAME, Common\Datastore $Config, Atlantis\Engine $App){
			require($__FILENAME);
			return;
		})($File, $this->Config, $this);

		////////

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
		(function(string $__FILENAME, Common\Datastore $Config, Atlantis\Engine $App){
			require($__FILENAME);
			return;
		})($File, $this->Config, $this);

		return $this;
	}

	protected function
	LoadRequiredLibraries():
	static {

		($this->Library)
		->Shove('Common', new Common\Library(Config: $this->Config, App: $this))
		->Shove('Avenue', new Avenue\Library(Config: $this->Config, App: $this))
		->Shove('Surface', new Surface\Library(Config: $this->Config, App: $this))
		->Shove('Storage', new Storage\Library(Config: $this->Config, App: $this))
		->Shove('Database', new Database\Library(Config: $this->Config, App: $this))
		->Shove('User', new User\Library(Config: $this->Config, App: $this))
		->Shove('Email', new Email\Library(Config: $this->Config, App: $this))
		->Shove('Atlantis', new Atlantis\Library(Config: $this->Config, App: $this));

		return $this;
	}

	protected function
	LoadAdditionalLibraries():
	static {

		$Classes = $this->Config[Key::ConfLibraries];
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

	static public function
	From(iterable $Pile):
	static {

		$Item = NULL;
		$Found = NULL;

		foreach($Pile as $Item)
		if($Item instanceof self) {
			$Found = $Item;
			break;
		}

		if($Found === NULL)
		throw new Atlantis\Error\EngineNotFound;

		return $Found;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

		return $this->FromConfEnv($File);
	}

	#[Common\Meta\Deprecated('2024-06-25', 'use Util::RewriteURL instead.')]
	public function
	RewriteURL(string $URL, ?Common\Datastore $Tags=NULL):
	string {



		return Util::RewriteURL($URL, $Tags, $this);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

}