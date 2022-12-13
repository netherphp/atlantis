<?php

namespace Nether\Atlantis;

use Nether;
use Nether\Avenue;
use Nether\User;

use Nether\Object\Datastore;

class Library
extends Nether\Common\Library {

	const
	ConfProjectName           = 'Project.Name',
	ConfProjectDesc           = 'Project.Desc',
	ConfProjectDescShort      = 'Project.DescShort',
	ConfProjectKeywords       = 'Project.Keywords',
	ConfProjectDefineConsts   = 'Project.DefineConstants',
	ConfProjectInitWithConfig = 'Project.InitWithConfig',
	ConfProjectWebRoot        = 'Project.WebRoot';

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
	ConfLibraries         = 'Nether.Atlantis.Libraries',
	ConfLogFormat         = 'Nether.Atlantis.Log.Format',
	ConfPassMinLen        = 'Nether.Atlantis.Passwords.MinLen',
	ConfPassReqAlphaLower = 'Nether.Atlantis.Passwords.RequireAlphaLower',
	ConfPassReqAlphaUpper = 'Nether.Atlantis.Passwords.RequireAlphaUpper',
	ConfPassReqNumeric    = 'Nether.Atlantis.Passwords.RequireNumeric',
	ConfPassReqSpecial    = 'Nether.Atlantis.Passwords.RequireSpecial',
	ConfUserAllowSignup   = 'Nether.Atlantis.Users.AllowSignup',
	ConfUserEmailActivate = 'Nether.Atlantis.Users.EmailActivation',
	ConfUserRequireAlias  = 'Nether.Atlantis.Users.RequireAlias';

	const
	WebServerTypeNone     = NULL,
	WebServerTypeApache24 = 'apache24';

	static public function
	InitDefaultConfig(?Datastore $Config = NULL):
	Datastore {

		$Config = parent::InitDefaultConfig($Config);

		$Config->BlendRight([
			static::ConfLogFormat         => 'default',
			static::ConfPassMinLen        => 10,
			static::ConfPassReqAlphaLower => TRUE,
			static::ConfPassReqAlphaUpper => TRUE,
			static::ConfPassReqNumeric    => TRUE,
			static::ConfPassReqSpecial    => TRUE,
			static::ConfUserAllowSignup   => TRUE,
			static::ConfUserEmailActivate => TRUE,
			static::ConfUserRequireAlias  => FALSE
		]);

		return $Config;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Init(...$Argv):
	void {

		static::OnInit(...$Argv);
		return;
	}

	static public function
	OnInit(?Datastore $Config=NULL, ...$Argv):
	void {

		static::InitDefaultConfig($Config);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Prepare(...$Argv):
	void {

		static::OnPrepare(...$Argv);
		return;
	}

	static public function
	OnPrepare(Nether\Atlantis\Engine $App, Datastore $Config, ...$Argv):
	void {

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

}

