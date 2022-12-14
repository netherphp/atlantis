<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\User;

use Nether\Object\Datastore;

class Library
extends Common\Library
implements
	Atlantis\Plugins\DashboardSidebarInterface {

	const
	ConfProjectID             = 'Project.Key',
	ConfProjectName           = 'Project.Name',
	ConfProjectDesc           = 'Project.Desc',
	ConfProjectDescShort      = 'Project.DescShort',
	ConfProjectKeywords       = 'Project.Keywords',
	ConfProjectDefineConsts   = 'Project.DefineConstants',
	ConfProjectInitWithConfig = 'Project.InitWithConfig',
	ConfProjectWebRoot        = 'Project.WebRoot',
	ConfProjectWebserver      = 'Project.WebServerType';

	const
	ConfAcmePhar       = 'AcmePHP.Phar',
	ConfAcmeCertRoot   = 'AcmePHP.CertRoot',
	ConfAcmeDomain     = 'AcmePHP.Domain',
	ConfAcmeEmail      = 'AcmePHP.Email',
	ConfAcmeAltDomains = 'AcmePHP.AltDomains',
	ConfAcmeCountry    = 'AcmePHP.Country',
	ConfAcmeCity       = 'AcmePHP.City',
	ConfAcmeOrgName    = 'AcmePHP.OrgName';

	const
	ConfLibraries           = 'Nether.Atlantis.Libraries',
	ConfLogFormat           = 'Nether.Atlantis.Log.Format',
	ConfPassMinLen          = 'Nether.Atlantis.Passwords.MinLen',
	ConfPassReqAlphaLower   = 'Nether.Atlantis.Passwords.RequireAlphaLower',
	ConfPassReqAlphaUpper   = 'Nether.Atlantis.Passwords.RequireAlphaUpper',
	ConfPassReqNumeric      = 'Nether.Atlantis.Passwords.RequireNumeric',
	ConfPassReqSpecial      = 'Nether.Atlantis.Passwords.RequireSpecial',
	ConfUserAllowLogin      = 'Nether.Atlantis.Users.AllowLogin',
	ConfUserAllowSignup     = 'Nether.Atlantis.Users.AllowSignup',
	ConfUserAllowSignupGank = 'Nether.Atlantis.Users.AllowSignupGank',
	ConfUserEmailActivate   = 'Nether.Atlantis.Users.EmailActivation',
	ConfUserRequireAlias    = 'Nether.Atlantis.Users.RequireAlias';

	const
	WebServerTypeNone     = NULL,
	WebServerTypeApache24 = 'apache24';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnLoad(...$Argv):
	void {

		static::$Config->BlendRight([
			static::ConfLogFormat           => 'default',
			static::ConfPassMinLen          => 10,
			static::ConfPassReqAlphaLower   => TRUE,
			static::ConfPassReqAlphaUpper   => TRUE,
			static::ConfPassReqNumeric      => TRUE,
			static::ConfPassReqSpecial      => TRUE,
			static::ConfUserAllowLogin      => FALSE,
			static::ConfUserAllowSignup     => FALSE,
			static::ConfUserAllowSignupGank => FALSE,
			static::ConfUserEmailActivate   => TRUE,
			static::ConfUserRequireAlias    => FALSE
		]);

		return;
	}

	public function
	OnReady(... $Argv):
	void {

		$App = $Argv['App'];

		$App->User = User\EntitySession::Get();

		if($App->User && $App->User->IsAdmin())
		$App->Log->InitAdminlog(
			$App->GetProjectRoot(),
			$App->Config[self::ConfLogFormat]
		);

		////////

		if($App->Router->GetSource() === 'dirscan') {
			$RouterPath = dirname(__FILE__);
			$Scanner = new Avenue\RouteScanner("{$RouterPath}/Routes");
			$Map = $Scanner->Generate();

			////////

			$Map['Verbs']->Each(
				fn(Datastore $Handlers)
				=> $App->Router->AddHandlers($Handlers)
			);

			$Map['Errors']->Each(
				fn(Avenue\Meta\RouteHandler $Handler, int $Code)
				=> $App->Router->AddErrorHandler($Code, $Handler)
			);
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	// DashboardSidebarInterface ///////////////////////////////////

	public function
	OnDashboardSidebar(Atlantis\Engine $App, Datastore $Sidebar):
	void {

		if($App->User)
		$Sidebar->Push(new Atlantis\Dashboard\AtlantisAccountSidebar);

		if($App->User && $App->User->IsAdmin())
		$Sidebar->Push(new Atlantis\Dashboard\AtlantisAdminSidebar);

		return;
	}

}

