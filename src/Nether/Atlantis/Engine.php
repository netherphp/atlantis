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

	public function
	__Construct(Nether\Object\Datastore $Config) {

		$this->Config = $Config;
		$this->Router = new Nether\Avenue\Router($this->Config);
		$this->Surface = new Nether\Surface\Engine($this->Config);

		return;
	}

	public function
	Run():
	static {

		$this->Router->Run(new Nether\Object\Datastore([
			'App'=> $this
		]));

		return $this;
	}

}