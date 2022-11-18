<?php

namespace Nether\Atlantis;
use Nether;

use Nether\Object\Datastore;
use Nether\Atlantis\Filter;
use Nether\Atlantis\Util;
use Nether\Atlantis\Library;

class PublicWeb
extends Nether\Avenue\Route {
/*// provides a basic route template for public endpoints that need to interact
as html pages. //*/

	protected bool
	$IsDone = FALSE;

	protected Nether\Atlantis\Engine
	$App;

	protected Nether\Object\Datastore
	$Config;

	protected ?Nether\User\Entity
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
		->Queue('BuildGlobalScope', $this->BuildGlobalScope(...), TRUE)
		->Set('Page.Title', NULL)
		->Set('Page.Desc', NULL)
		->Set('Page.Keywords', NULL)
		->CaptureBegin();

		return;
	}

	public function
	OnDone():
	void {

		$this->IsDone = TRUE;

		if(!$this->App->Surface->IsCapturing())
		return;

		$this
		->PreparePageTitle()
		->PreparePageDesc()
		->PreparePageKeywords();

		($this->App->Surface)
		->CaptureEnd()
		->Render();

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
	BuildGlobalScope(array &$Dataset):
	void {

		// application objects.

		$Dataset['App'] = $this->App;
		$Dataset['Router'] = $this->App->Router;
		$Dataset['Route'] = $this;
		$Dataset['User'] = $this->App->User;
		$Dataset['CacheBuster'] = md5(microtime(TRUE));

		// printing callables.

		$Dataset['Printer'] = Util::PrintHTML(...);

		// returning callables.

		$Dataset['Encoder'] = Filter::EncodeHTML(...);
		$Dataset['Selected'] = Util::GetSelectedHTML(...);
		$Dataset['Checked'] = Util::GetCheckedHTML(...);

		// theme callables that a bit cheeky.

		$Dataset['GetThemeURL'] = (
			function(string $In, ?string $Theme=NULL) use($Dataset) {
				$Theme ??= $Dataset['App']->Surface->GetTheme();
				return "/themes/{$Theme}/{$In}";
			}
		);

		$Dataset['ThemeURL'] = (
			function(string $In, ?string $Theme=NULL) use($Dataset) {
				echo $Dataset['GetThemeURL']($In, $Theme);
				return;
			}
		);

		return;
	}

	protected function
	PreparePageTitle():
	static {

		$Name = $this->App->Config[Library::ConfProjectName];
		$Desc = $this->App->Config[Library::ConfProjectDescShort];

		////////

		if($this->App->Surface->Has('Page.Title'))
		$this->App->Surface->Set('Page.Title', sprintf(
			'%s - %s',
			$this->App->Surface->Get('Page.Title'),
			$Name
		));

		elseif($Desc)
		$this->App->Surface->Set('Page.Title', sprintf(
			'%s - %s',
			$Name,
			$Desc
		));

		else
		$this->App->Surface->Set('Page.Title', $Name);

		////////

		return $this;
	}

	protected function
	PreparePageDesc():
	static {

		if($this->App->Surface->Get('Page.Desc') === FALSE)
		$this->App->Surface->Set(
			'Page.Desc',
			$this->App->Config[Library::ConfProjectDesc]
			?? ''
		);

		return $this;
	}

	protected function
	PreparePageKeywords():
	static {

		$Existing = $this->App->Surface->Get('Page.Keywords');

		////////

		if(is_string($Existing))
		$Existing = explode(',', $Existing);

		if(is_array($Existing))
		$Existing = new Datastore($Existing);

		if(!($Existing instanceof Datastore))
		$Existing = new Datastore;

		////////

		$Existing->Remap(fn(string $Item) => trim($Item));

		$this->App->Surface->Set(
			'Page.Keywords',
			$Existing
		);

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
	IsUserAdmin(int $Min):
	bool {

		return (
			TRUE
			&& ($this->App->User instanceof Nether\User\Entity)
			&& ($this->App->User->Admin >= $Min)
		);
	}

	public function
	Quit(int $Err=0, ?string $Msg=NULL, ?string $Title=NULL):
	void {

		if($Err !== 0) {
			$Title ??= 'Error';
			$Msg ??= 'There was an error processing your request.';

			($this->App->Surface)
			->Set('Error', $Err)
			->Set('Title', $Title)
			->Set('Message', $Msg)
			->Area('error/error');
		}

		exit(0);
		return;
	}

}
