<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Surface;
use Nether\User;

use Nether\Common\Datastore;
use Nether\Common\Datafilter;
use Nether\Atlantis\Meta\UserActivationFlow;
use Nether\Atlantis\Meta\TrafficReportSkip;

use Exception;

class PublicWeb
extends Avenue\Route {
/*// provides a basic route template for public endpoints that need to interact
as html pages. //*/

	public Datafilter
	$Query;

	public Datafilter
	$Data;

	public ?Atlantis\Struct\TrafficRow
	$Hit = NULL;

	////////

	protected bool
	$IsDone = FALSE;

	public Atlantis\Engine
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

		////////

		if($this->HandleAppDevProdSendoff())
		return Avenue\Response::CodeRedirectPerm;

		////////

		//return Avenue\Response::CodeOK;
		return $this->Response->Code;
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

		if(!defined('UNIT_TEST_GO_BRRRT'))
		if($Code >= 300 && $Code <= 399)
		return;

		////////

		($this->App->Surface)
		->Queue('BuildGlobalScope', $this->BuildGlobalScope(...), TRUE)
		->Set('Page.Title', NULL)
		->Set('Page.Desc', NULL)
		->Set('Page.Keywords', NULL)
		->CaptureBegin();

		////////

		if($Code >= 400) {
			// terminate the app if there is no custom error handler for
			// whatever state we landed in.

			if(!($Handler instanceof Avenue\Meta\ErrorHandler))
			$this->Quit($Code);
		}

		////////

		$this->HandleUserOnboarding($Info);

		// @todo 2023-07-25 handle not calling traffic reporting if the
		// user agent matches the soft ignore.

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

		$Name = $this->App->Config[Key::ConfProjectName];
		$Desc = $this->App->Config[Key::ConfProjectDescShort];

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
			$this->App->Config[Key::ConfProjectDesc]
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
		if($this->Config[Key::ConfUserEmailActivate])
		if(!$MethodInfo->HasAttribute(Atlantis\Meta\UserActivationFlow::class))
		$this->Goto('/login/activate');

		// handle if the account has not had an alias set yet.

		if($this->User->Alias === NULL)
		if($this->Config[Key::ConfUserRequireAlias])
		if(!$MethodInfo->HasAttribute(Atlantis\Meta\UserActivationFlow::class))
		$this->Goto('/login/activate');

		return;
	}

	protected function
	HandleTrafficReporting(Common\Prototype\MethodInfo $MethodInfo):
	void {

		if(!$this->App->Config[Atlantis\Key::ConfTrafficReporting])
		return;

		// dont log things that are in the soft ignore list.

		$IgnoreUA = Library::Get(Key::ConfAccessIgnoreAgentSoft);
		$InputUA = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : NULL;

		if($IgnoreUA && $InputUA) {
			if(preg_match(sprintf('#\b(?:%s)\b#msi', $IgnoreUA), $InputUA))
			return;
		}

		// @todo 2023-07-21
		// this unit test const should be replaced with a database
		// availability check instead.

		if(defined('UNIT_TEST_GO_BRRRT'))
		return;

		// do not log traffic on routes that specifically flagged
		// themselves as unactionable.

		if($MethodInfo->HasAttribute(Atlantis\Meta\TrafficReportSkip::class))
		return;

		// do not log admin traffic except on dev instances, that way the
		// traffic dashboard has something to do.

		if($this->IsUserAdmin()) {
			if(!$this->App->IsDev())
			return;
		}

		$Since = new Common\Date('-10 min');
		$Hash = $this->Request->GetTrafficHash();
		$UserID = isset($this->App->User) ? $this->App->User->ID : NULL;
		$Parts = parse_url($Hash->URL);
		$Domain = $this->App->Config[Atlantis\Key::ConfProjectDomain];
		$Path = NULL;
		$Query = NULL;
		$UserAgent = NULL;
		$From = NULL;
		$FromURL = NULL;
		$FromDomain = NULL;
		$FromPath = NULL;
		$FromQuery = NULL;

		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			$UserAgent = $_SERVER['HTTP_USER_AGENT'];
		}

		if(isset($_SERVER['HTTP_REFERER'])) {
			$FromURL = $_SERVER['HTTP_REFERER'];
			$From = parse_url($FromURL);

			if(is_array($From)) {
				if(isset($From['host']))
				$FromDomain = $From['host'];

				if(isset($From['path']))
				$FromPath = $From['path'];

				if(isset($From['query']))
				$FromQuery = $From['query'];
			}
		}

		////////

		if(!$Parts)
		return;

		//if(isset($Parts['host']))
		//$Domain = $Parts['host'];

		if(isset($Parts['path']))
		$Path = $Parts['path'];

		if(isset($Parts['query']))
		$Query = $Parts['query'];

		// don't self reference in the from fields.

		if($FromDomain === $this->App->Config[Key::ConfProjectDomain])
		$FromDomain = NULL;

		if($FromDomain !== NULL)
		$FromDomain = (string)Common\Struct\Domain::FromDomain($FromDomain, 2);

		// don't self reference in the from fields.
		// (trying again after the clean)

		if($FromDomain === $this->App->Config[Key::ConfProjectDomain])
		$FromDomain = NULL;

		////////

		if($this->App->Database->Exists(Struct\TrafficRow::$DBA)) {
			$Old = Struct\TrafficRow::Find([
				'Hash'    => $Hash->Get(),
				'Since'   => $Since->GetUnixtime(),
				'Limit'   => 1
			]);

			if($Old->Count() === 0)
			$this->Hit = Struct\TrafficRow::Insert([
				'Hash'       => $Hash->Get(),
				'Visitor'    => $Hash->GetVisitorHash(),
				'IP'         => $Hash->IP,
				'URL'        => $Hash->URL,
				'UserID'     => $UserID,
				'Domain'     => $Domain,
				'Path'       => $Path,
				'Query'      => $Query,
				'UserAgent'  => $UserAgent,
				'FromURL'    => $FromURL,
				'FromDomain' => $FromDomain,
				'FromPath'   => $FromPath,
				'FromQuery'  => $FromQuery
			]);
		}

		return;
	}

	protected function
	HandleAppDevProdSendoff():
	bool {

		$Machine = new Util\DevProdSendOffMachine($this->App);
		$Should = $Machine->ShouldSendOff();

		if(!$Should)
		return FALSE;

		////////

		$GotoURL = $Machine->GetURL();

		if($GotoURL === $this->App->Router->Request->GetURL())
		throw new Exception('sendoff rewriter causing loop');

		($this->App->Router->Response)
		->SetHeader('Location', $GotoURL);

		return TRUE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetApp(Atlantis\Engine $App):
	static {

		$this->App = $App;

		return $this;
	}

	public function
	SetQuery(Datafilter $In):
	static {

		$this->Query = $In;

		return $this;
	}

	public function
	SetData(Datafilter $In):
	static {

		$this->Data = $In;

		return $this;
	}

	public function
	SetDone(bool $State=TRUE):
	static {

		$this->IsDone = $State;

		return $this;
	}

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

		//if(!defined('UNIT_TEST_GO_BRRRT'))
		//exit(0);

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

		($this->Surface)
		->Area('error/error', [
			'Error'   => $Code,
			'Title'   => $Title,
			'Message' => $Message
		]);

		$this->Quit();
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
