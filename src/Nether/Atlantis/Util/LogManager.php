<?php

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
	/*//
	@date 2022-11-26
	set up logging to be good enough such that all the early on stuff of
	the framework can send to it, buffering all the messages it will get.
	//*/

		$this->App = new Monolog\Logger('App');

		////////

		$Path = sprintf('%s/logs/%s', $this->ProjectRoot, date('Y/m'));
		Atlantis\Util::MkDir($Path);

		////////

		$this->App->_nStream = new Monolog\Handler\StreamHandler(
			sprintf('%s/atlantis.log', $Path),
			Monolog\Level::Debug
		);

		$this->App->_nBuffer = new Monolog\Handler\BufferHandler(
			$this->App->_nStream
		);

		$this->App->_nFormatter = NULL;

		////////

		$this->App->PushHandler($this->App->_nBuffer);
		//$this->App->Info('HEY', [ 'PHP'=> php_sapi_name() ]);

		return $this;
	}

	public function
	Update(string $Format):
	static {
	/*//
	@date 2022-11-26
	after configuration has been read we can update the logging settings
	to whatever was selected. then rewrire the logging so that the stream
	writing is no longer buffered.
	//*/

		// set the formatting to the selected one in the config file.

		$this->Format = $Format;

		$this->App->_nFormatter = match($this->Format) {
			'json'  => new Monolog\Formatter\JsonFormatter,
			default => NULL
		};

		if($this->App->_nFormatter)
		$this->App->_nStream->SetFormatter(
			$this->App->_nFormatter
		);

		// pull the buffer out and destroy it. this should trigger a write
		// of what was buffered.

		$this->App->PopHandler();
		$this->App->_nBuffer->Close();

		unset($this->App->_nBuffer);
		$this->App->_nBuffer = NULL;

		// reset that stream we were using in the buffer and proceed
		// with live writing to the log.

		$this->App->_nStream->Reset();
		$this->App->PushHandler($this->App->_nStream);
		//$this->App->Info('HAY', [ 'PHP'=> php_sapi_name() ]);

		return $this;
	}

	public function
	Main(string $Message, array $Context=[], Monolog\Level $Level=Monolog\Level::Info):
	static {

		if($this->App instanceof Monolog\Logger)
		$this->App->Log($Level, $Message, $Context);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	InitAdminLog():
	static {

		$this->Admin = new Monolog\Logger('Admin');

		////////

		$this->Admin->_nStream = new Monolog\Handler\StreamHandler(
			sprintf('%s/logs/%s/admin.log', $this->ProjectRoot, date('Y/m')),
			Monolog\Level::Debug
		);

		$this->Admin->_nFormatter = match($this->Format) {
			'json'  => new Monolog\Formatter\JsonFormatter,
			default => NULL
		};

		if($this->Admin->_nFormatter)
		$this->Admin->_nStream->SetFormatter(
			$this->Admin->_nFormatter
		);

		////////

		$this->Admin->PushHandler($this->Admin->_nStream);
		//$this->Admin->Info('HEY', [ 'PHP'=> php_sapi_name() ]);

		return $this;
	}

	public function
	Admin(string $Message, array $Context=[], Monolog\Level $Level=Monolog\Level::Info):
	static {

		if(!$this->Admin instanceof Monolog\Logger)
		$this->InitAdminLog();

		$this->Admin->Log($Level, $Message, $Context);

		return $this;
	}

}
