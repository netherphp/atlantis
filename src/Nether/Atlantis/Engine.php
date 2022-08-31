<?php

namespace Nether\Atlantis;
use Nether;

class Engine {

	public Nether\Object\Datastore
	$Config;

	public Nether\Avenue\Router
	$Router;

	public Nether\Surface\Engine
	$Surface;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected string
	$ProjectRoot;

	protected float
	$ProjectTime;

	protected string
	$ProjectEnv;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ProjectRoot, ?Nether\Object\Datastore $Conf=NULL) {

		// prepare some defaults.

		$this->ProjectTime = microtime(TRUE);
		$this->ProjectRoot = $ProjectRoot;
		$this->ProjectEnv = 'dev';

		// load in configuration.

		$this
		->DetermineEnvironment()
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

		return ($this->ProjectEnv === $Env);
	}

	public function
	IsDev():
	bool {

		return $this->IsEnv('dev');
	}

	public function
	IsProd():
	bool {

		return !$this->IsEnv('dev');
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
	LoadProjectConfig():
	static {

		$this->Config = new Nether\Object\Datastore;

		$this->Config->Read(sprintf(
			'%s/conf/config.json',
			$this->ProjectRoot
		));

		return $this;
	}

	protected function
	LoadEnvironmentConfig():
	static {

		$EnvFile = sprintf(
			'%s/conf/env/%s/config.json',
			$this->ProjectRoot,
			$this->ProjectEnv
		);

		////////

		if(!file_exists($EnvFile))
		return $this;

		if(!is_readable($EnvFile))
		return $this;

		////////

		$this->Config->Read($EnvFile, TRUE);

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