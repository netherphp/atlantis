<?php

namespace Nether\Atlantis\Routes;
use Nether;

use Nether\Object\Datastore;
use Nether\Atlantis\Filter;
use Nether\Atlantis\Util;
use Nether\Atlantis\Library;

class Web
extends Nether\Avenue\Route {

	protected Nether\Atlantis\Engine
	$App;

	public function
	OnWillConfirmReady(?Datastore $Input):
	void {

		$this->App = $Input['App'];

		return;
	}

	public function
	OnReady(?Datastore $Input):
	void {

		$this->OnWillConfirmReady($Input);

		($this->App->Surface)
		->Queue('BuildGlobalScope', $this->BuildGlobalScope(...), TRUE)
		->Set('Page.Title', FALSE)
		->Set('Page.Desc', FALSE)
		->Set('Page.Keywords', FALSE)
		->CaptureBegin();

		return;
	}

	public function
	OnDone():
	void {

		if($this->App->Surface->Get('Page.Title') === FALSE)
		$this->App->Surface->Set(
			'Page.Title',
			$this->App->Config[Library::ConfProjectName]
			?? ''
		);

		if($this->App->Surface->Get('Page.Desc') === FALSE)
		$this->App->Surface->Set(
			'Page.Desc',
			$this->App->Config[Library::ConfProjectDesc]
			?? ''
		);

		if($this->App->Surface->Get('Page.Desc') === FALSE)
		$this->App->Surface->Set(
			'Page.Desc',
			$this->App->Config[Library::ConfProjectDesc]
			?? new Datastore
		);

		($this->App->Surface)
		->CaptureEnd()
		->Render();

		return;
	}

	public function
	BuildGlobalScope(array &$Dataset):
	void {

		// application objects.

		$Dataset['App'] = $this->App;
		$Dataset['Router'] = $this->App->Router;
		$Dataset['Route'] = $this;

		// printing callables.

		$Dataset['Printer'] = Util::PrintHTML(...);

		// returning callables.

		$Dataset['Encoder'] = Filter::EncodeHTML(...);
		$Dataset['Selected'] = Util::GetSelectedHTML(...);
		$Dataset['Checked'] = Util::GetCheckedHTML(...);

		return;
	}

}
