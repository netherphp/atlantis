<?php

namespace Nether\Atlantis;
use Nether;

use Nether\Avenue;
use Nether\Common;

class PublicAPI
extends Nether\Avenue\Route {
/*//
@date 2022-11-09
provides a basic route template for public endpoints that need to interact
as json apis. all output is wrapped in a standardised json message. this version
does not do any additional access checking.
//*/

	#[Common\Meta\PropertyFactory('FromArray')]
	const
	QuitMsg = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected bool
	$IsDone = FALSE;

	protected Nether\Atlantis\Engine
	$App;

	protected Nether\Common\Datafilter
	$Query;

	protected Nether\Common\Datafilter
	$Data;

	protected Nether\Common\Datastore
	$Config;

	protected ?Nether\User\EntitySession
	$User;

	public function
	OnWillConfirmReady(?Avenue\Struct\ExtraData $ExtraData):
	int {

		$this->App = $ExtraData['App'];
		$this->User = $this->App->User;
		$this->Config = $this->App->Config;
		$this->Query = clone($this->Request->Query);
		$this->Data = clone($this->Request->Data);

		return Avenue\Response::CodeOK;
	}

	public function
	OnReady(?Avenue\Struct\ExtraData $ExtraData):
	void {

		$this->OnWillConfirmReady($ExtraData);

		($this->App->Surface)
		->Set('API.Error', 0)
		->Set('API.Message', 'OK')
		->Set('API.Goto', NULL)
		->Set('API.Payload', NULL)
		->CaptureBegin();

		($this->Response)
		->SetContentType(Nether\Avenue\Response::ContentTypeJSON);

		return;
	}

	public function
	OnDone():
	void {

		$this->IsDone = TRUE;

		($this->App->Surface)
		->CaptureEnd();

		($this->Response)
		->SetContentType(Nether\Avenue\Response::ContentTypeJSON);

		$Dataset = [
			'Error'   => $this->App->Surface->Get('API.Error'),
			'Message' => $this->App->Surface->Get('API.Message'),
			'Goto'    => $this->App->Surface->Get('API.Goto'),
			'Payload' => $this->App->Surface->Get('API.Payload')
		];

		if($this->App->IsDev())
		$Dataset['STDOUT'] = $this->App->Surface->GetContent() ?: NULL;

		echo json_encode($Dataset);
		return;
	}

	public function
	OnDestroy():
	void {

		if(isset($this->App) && !$this->IsDone)
		$this->OnDone();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetError():
	int {

		return $this->App->Surface->Get('API.Error') ?: 0;
	}

	public function
	SetError(int $Code, ?string $Msg=NULL):
	static {

		($this->App->Surface)
		->Set('API.Error', $Code);

		if($Msg !== NULL) {
			($this->App->Surface)
			->Set('API.Message', $Msg);
		}

		return $this;
	}

	public function
	GetMessage():
	string {

		return $this->App->Surface->Get('API.Message') ?: 'OK';
	}

	public function
	SetMessage(string $Msg):
	static {

		($this->App->Surface)
		->Set('API.Message', $Msg);

		return $this;
	}

	public function
	GetGoto():
	?string {

		return $this->App->Surface->Get('API.Goto') ?: NULL;
	}

	public function
	SetGoto(?string $URL):
	static {

		($this->App->Surface)
		->Set('API.Goto', $URL);

		return $this;
	}

	public function
	GetPayload():
	mixed {

		return $this->App->Surface->Get('API.Payload') ?: NULL;
	}

	public function
	SetPayload(?array $Dataset):
	static {

		($this->App->Surface)
		->Set('API.Payload', $Dataset);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetHeader(string $Name, mixed $Value):
	static {

		$this->Response->SetHeader($Name, $Value);

		return $this;
	}

	public function
	HasUser():
	bool {

		return ($this->App->User instanceof Nether\User\Entity);
	}

	public function
	IsUserAdmin(int $Min=1):
	bool {

		return (
			TRUE
			&& ($this->App->User instanceof Nether\User\Entity)
			&& ($this->App->User->Admin >= $Min)
		);
	}

	public function
	Quit(int $Err, ?string $Msg=NULL):
	void {

		$Msg = match(TRUE) {
			($Msg !== NULL)
			=> $Msg,

			(isset(static::QuitMsg[$Err]))
			=> static::QuitMsg[$Err],

			($Err !== 0)
			=> 'Error',

			default
			=> 'OK'
		};

		$this
		->SetError($Err)
		->SetMessage($Msg);

		throw new Error\Quit($Err, $Msg);
		return;
	}

	#[Common\Meta\Date('2023-10-24')]
	#[Common\Meta\Info('If a dev env it will show the error and quit. Else it will show a generic error and quit.')]
	public function
	ErrorDevQuit(string $Title='Error', ?string $Message=NULL, int $Code=-1):
	void {

		if($this->App->GetProjectEnvType() !== 'dev') {
			$Code = NULL;
			$Title = 'System Error';
			$Message = 'Something has gone wrong.';
		}

		////////

		if($Title && $Message)
		$Title .= ": {$Message}";

		////////

		$this->Quit($Code, $Title);

		return;
	}


}
