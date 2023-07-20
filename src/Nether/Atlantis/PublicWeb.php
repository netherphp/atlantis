<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
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
	__RewireDoNothing():
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnWillConfirmReady(?Avenue\Struct\ExtraData $ExtraData):
	int {

		$this->App = $ExtraData['App'];
		$this->User = $this->App->User;
		$this->Config = $this->App->Config;
		$this->Surface = $this->App->Surface;

		$this->Query = clone($this->Request->Query);
		$this->Data = clone($this->Request->Data);

		$Handler = $this->App->Router->GetCurrentHandler();
		$Info = static::GetMethodInfo($Handler->Method);

		// prototype code
		// if we are viewing a dev server and we're not an admin then we
		// should gtfo.

		if($this->App->IsDev() && !$this->IsUserAdmin()) {
			if(!$Info->HasAttribute(Atlantis\Meta\UserActivationFlow::class)) {

				$Rewriter = match(TRUE) {
					(is_callable(Library::Get(Library::ConfDevLinkRewriter)))
					=> Library::Get(Library::ConfDevLinkRewriter),

					default
					=> fn(string $URL)=> preg_replace('#://dev\.#', '://', $URL)
				};

				$Goto = $Rewriter($this->App->Router->Request->GetURL());

				// if no rewriter was defined or it caused a loop then
				// should just fail.

				if($Goto === $this->App->Router->Request->GetURL())
				return Avenue\Response::CodeForbidden;

				// else we can redirect to the rewritten url.

				($this->App->Router->Response)
				->SetHeader('Location', $Goto);

				return Avenue\Response::CodeRedirectPerm;
			}
		}

		return Avenue\Response::CodeOK;
	}

	public function
	OnReady(?Datastore $ExtraData):
	void {

		$Code = $this->OnWillConfirmReady($ExtraData);
		$Handler = $this->App->Router->GetCurrentHandler();
		$Info = static::GetMethodInfo($Handler->Method);

		($this->App->Router->Response)
		->SetCode($Code);

		// handle if the WillConfirm returned a redirect request.

		if($Code >= 300 && $Code <= 399)
		exit(0);

		////////

		($this->App->Surface)
		->Queue('BuildGlobalScope', $this->BuildGlobalScope(...), TRUE)
		->Set('Page.Title', NULL)
		->Set('Page.Desc', NULL)
		->Set('Page.Keywords', NULL)
		->CaptureBegin();

		////////

		if($Code >= 400)
		$this->Quit($Code);

		////////

		$this->HandleUserOnboarding($Info);
		$this->HandleTrafficReporting($Info);

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
		$Dataset['Util'] = new Atlantis\Struct\TemplateHelper($this->App);

		// deprecated references.

		$Dataset['CacheBuster'] = $Dataset['Util']->GetCacheBuster();
		$Dataset['Printer'] = $Dataset['Util']->Print(...);
		$Dataset['Encoder'] = $Dataset['Util']->Encode(...);
		$Dataset['Selected'] = $Dataset['Util']->GetSelectedHTML(...);
		$Dataset['Checked'] = $Dataset['Util']->GetCheckedHTML(...);
		$Dataset['GetThemeURL'] = $Dataset['Util']->GetThemeURL(...);
		$Dataset['ThemeURL'] = $Dataset['Util']->ThemeURL(...);

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
	HandleUserOnboarding(Common\Prototype\MethodInfo $MethodInfo):
	void {

		// handle if there is even a user.

		if(!isset($this->User))
		return;

		// handle if the account has been banned.

		if($this->User->TimeBanned !== 0)
		$this->Quit(403, 'Account is banned.');

		// handle if the account has not yet been activated.

		if(!$this->User->Activated)
		if($this->Config[Atlantis\Library::ConfUserEmailActivate])
		if(!$MethodInfo->HasAttribute(Atlantis\Meta\UserActivationFlow::class))
		$this->Goto('/login/activate');

		// handle if the account has not had an alias set yet.

		if($this->User->Alias === NULL)
		if($this->Config[Atlantis\Library::ConfUserRequireAlias])
		if(!$MethodInfo->HasAttribute(Atlantis\Meta\UserActivationFlow::class))
		$this->Goto('/login/activate');

		return;
	}

	protected function
	HandleTrafficReporting(Common\Prototype\MethodInfo $MethodInfo):
	void {

		if($MethodInfo->HasAttribute(Atlantis\Meta\TrafficReportSkip::class))
		return;

		if($this->IsUserAdmin())
		return;

		$Since = new Common\Date('-5 min');
		$Hash = $this->Request->GetTrafficHash();
		$UserID = isset($this->App->User) ? $this->App->User->ID : NULL;
		$Parts = parse_url($Hash->URL);
		$Domain = NULL;
		$Path = NULL;
		$Query = NULL;

		////////

		if(!$Parts)
		return;

		if(isset($Parts['host']))
		$Domain = $Parts['host'];

		if(isset($Parts['path']))
		$Path = $Parts['path'];

		if(isset($Parts['query']))
		$Query = $Parts['query'];

		////////

		$Old = Struct\TrafficRow::Find([
			'Hash'    => $Hash->Get(),
			'Since'   => $Since->GetUnixtime(),
			'Limit'   => 1
		]);

		if($Old->Count() === 0)
		$Row = Struct\TrafficRow::Insert([
			'Hash'    => $Hash->Get(),
			'Visitor' => $Hash->GetVisitorHash(),
			'IP'      => $Hash->IP,
			'URL'     => $Hash->URL,
			'UserID'  => $UserID,
			'Domain'  => $Domain,
			'Path'    => $Path,
			'Query'   => $Query
		]);

		return;
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

		return ($this->App->User instanceof User\Entity);
	}

	public function
	IsUserAdmin(int $MinLvl=1):
	bool {

		return (
			TRUE
			&& ($this->App->User instanceof User\Entity)
			&& ($this->App->User->Admin >= $MinLvl)
		);
	}

	public function
	Quit(int $Err=0, ?string $Msg=NULL, ?string $Title=NULL):
	void {

		if($Err !== 0) {
			if($this->App->Router->Response->Code === Avenue\Response::CodeOK)
			$this->App->Router->Response->SetCode(Avenue\Response::CodeForbidden);

			////////

			$Title ??= 'Error';
			$Msg ??= 'There was an error processing your request.';

			($this->App->Surface)
			->Set('Error', $Err)
			->Set('Title', $Title)
			->Set('Message', $Msg)
			->Area('error/error');
		}


		$this->Handler->Method = '__RewireDoNothing';

		// after some back and forth i think this needs to just be made
		// super stupid such that if it detects the unit testing suite it
		// should not do this. the rewire trick works, but only if it was
		// a WillConfirmAnswer method and not the method itself.

		if(!defined('UNIT_TEST_GO_BRRRT'))
		exit(0);

		return;
	}

	public function
	QueryBlender(array $Input):
	string {

		return $this->Data->GetQueryString($Input);
	}

	public function
	QueryCooker(array $Input):
	string {

		return sprintf('?%s', $this->QueryBlender($Input));
	}

}
