<?php

namespace Nether\Atlantis\Routes;

use Nether;

class Web
extends Nether\Avenue\Route {

	protected Nether\Atlantis\Engine
	$App;

	public function
	OnWillConfirmReady(?Nether\Object\Datastore $Input):
	void {

		$this->App = $Input['App'];

		return;
	}

	public function
	OnReady(?Nether\Object\Datastore $Input):
	void {

		$this->OnWillConfirmReady($Input);

		($this->App->Surface)
		->Queue('BuildGlobalScope', $this->BuildGlobalScope(...), TRUE)
		->Set('Page.Title', 'PAGE TITLE')
		->Set('Page.Desc', 'PAGE DESCRIPTION')
		->Set('Page.Keywords', new Nether\Object\Datastore)
		->CaptureBegin();

		return;
	}

	public function
	OnDone():
	void {

		($this->App->Surface)
		->CaptureEnd()
		->Render();

		return;
	}

	public function
	BuildGlobalScope(array &$Dataset):
	void {

		$Dataset['App'] = $this->App;
		$Dataset['Router'] = $this->App->Router;
		$Dataset['Route'] = $this;

		return;
	}

}
