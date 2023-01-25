<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Surface;
use Nether\User;

use Nether\Common\Datastore;
use Nether\Common\Datafilter;

class PublicWeb
extends Avenue\Route {
/*// provides a basic route template for public endpoints that need to interact
as html pages. //*/

	public Datafilter
	$Query;

	public Datafilter
	$Data;

	////////

	protected bool
	$IsDone = FALSE;

	protected Atlantis\Engine
	$App;

	protected Surface\Engine
	$Surface;

	protected Datastore
	$Config;

	protected ?User\EntitySession
	$User;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnWillConfirmReady(?Datastore $Input):
	void {

		$this->App = $Input['App'];
		$this->User = $this->App->User;
		$this->Config = $this->App->Config;
		$this->Surface = $this->App->Surface;

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

		$this->HandleUserOnboarding();

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

	protected function
	HandleUserOnboarding():
	static {

		if($this->User instanceof User\Entity) {

			$Handler = $this->App->Router->GetCurrentHandler();
			$Info = static::GetMethodInfo($Handler->Method);

			// handle if the account has been banned.

			if($this->User->TimeBanned !== 0)
			$this->Quit(403, 'Account is banned.');

			// handle if the account has not yet been activated.

			if(!$this->User->Activated)
			if($this->Config[Atlantis\Library::ConfUserEmailActivate])
			if(!$Info->HasAttribute(Atlantis\Meta\UserActivationFlow::class))
			$this->Goto('/login/activate');

			// handle if the account has not had an alias set yet.

			if($this->User->Alias === NULL)
			if($this->Config[Atlantis\Library::ConfUserRequireAlias])
			if(!$Info->HasAttribute(Atlantis\Meta\UserActivationFlow::class))
			$this->Goto('/login/activate');

		}

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasUser():
	bool {

		return ($this->App->User instanceof User\Entity);
	}

	public function
	IsUserAdmin(int $Min):
	bool {

		return (
			TRUE
			&& ($this->App->User instanceof User\Entity)
			&& ($this->App->User->Admin >= $Min)
		);
	}

	public function
	Quit(int $Err=0, ?string $Msg=NULL, ?string $Title=NULL):
	void {

		if($Err !== 0) {
			$Title ??= 'Error';
			$Msg ??= 'There was an error processing your request.';

			($this->App->Router->Response)
			->SetCode(Avenue\Response::CodeForbidden);

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
