<?php

/* this class was originally built to house a monolog based thing but i am
extremely displeased with how i abstracted that so it has been nuked to
eliminate the issues it was causing. this skelton exists just to keep code
running and should be removed once a replacement is settled.
bob, 2024-02-23 */

// dear future you: dont try to reverse engineer and back fill this api.
// just make a better one and push it through.

namespace Nether\Atlantis\Util;

use Monolog;
use Nether\Atlantis;
use Nether\Common;

class LogManager {

	public ?Monolog\Logger
	$App = NULL;

	public ?Monolog\Logger
	$Admin = NULL;

	protected string
	$ProjectRoot;

	protected string
	$Format;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ProjectRoot) {
		$this->ProjectRoot = $ProjectRoot;
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Init():
	static {
		return $this;
	}

	public function
	Update(string $Format):
	static {
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Main(string $Message, array $Context=[], Monolog\Level $Level=Monolog\Level::Info):
	static {
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	InitAdminLog():
	static {
		return $this;
	}

	public function
	Admin(string $Message, array $Context=[], Monolog\Level $Level=Monolog\Level::Info):
	static {
		return $this;
	}

}
