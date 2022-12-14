<?php

namespace Nether\Atlantis;
use Nether;

use Nether\Object\Datastore;
use Nether\Atlantis\Filter;
use Nether\Atlantis\Util;
use Nether\Atlantis\Library;

class PublicAPI
extends Nether\Avenue\Route {
/*//
@date 2022-11-09
provides a basic route template for public endpoints that need to interact
as json apis. all output is wrapped in a standardised json message.
//*/

	protected bool
	$IsDone = FALSE;

	protected Nether\Atlantis\Engine
	$App;

	protected Nether\Object\Datafilter
	$Query;

	protected Nether\Object\Datafilter
	$Data;

	protected Nether\Object\Datastore
	$Config;

	protected ?Nether\User\EntitySession
	$User;

	public function
	OnWillConfirmReady(?Datastore $Input):
	void {

		$this->App = $Input['App'];
		$this->User = $this->App->User;
		$this->Config = $this->App->Config;
		$this->Query = clone($this->Request->Query);
		$this->Data = clone($this->Request->Data);

		return;
	}

	public function
	OnReady(?Datastore $Input):
	void {

		$this->OnWillConfirmReady($Input);

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

		if($Msg === NULL)
		$Msg = match($Err) {
			0       => 'OK',
			default => 'Error'
		};

		$this
		->SetError($Err)
		->SetMessage($Msg);

		exit($Err);
		return;
	}

}
