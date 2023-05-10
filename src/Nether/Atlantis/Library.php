<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\User;

use Nether\Common\Datastore;

class Library
extends Common\Library
implements
	Atlantis\Plugins\DashboardSidebarInterface,
	Atlantis\Plugins\AccessTypeDefineInterface {

	const
	ConfProjectID             = 'Project.Key',
	ConfProjectName           = 'Project.Name',
	ConfProjectDomain         = 'Project.Domain',
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
	ConfUserRequireAlias    = 'Nether.Atlantis.Users.RequireAlias',
	ConfContactTo           = 'Nether.Atlantis.Contact.To',
	ConfContactBCC          = 'Nether.Atlantis.Contact.BCC',
	ConfContactSubject      = 'Nether.Atlantis.Contact.Subject';

	const
	ConfPageEnableDB          = 'Nether.Atlantis.Page.EnableDatabase',
	ConfPageEnableStatic      = 'Nether.Atlantis.Page.EnableStatic',
	ConfPageStaticStorageKey  = 'Nether.Atlantis.Page.StaticStorageKey',
	ConfPageStaticStoragePath = 'Nether.Atlantis.Page.StaticStoragePath';

	const
	WebServerTypeNone     = NULL,
	WebServerTypeApache24 = 'apache24';

	const
	AccessContactLogManage = 'Nether.Atlantis.ContactLog.Manage',
	AccessPageManage       = 'Nether.Atlantis.Page.Manage';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnLoad(...$Argv):
	void {

		static::$Config->BlendRight([
			static::ConfLogFormat             => 'default',
			static::ConfPassMinLen            => 10,
			static::ConfPassReqAlphaLower     => TRUE,
			static::ConfPassReqAlphaUpper     => TRUE,
			static::ConfPassReqNumeric        => TRUE,
			static::ConfPassReqSpecial        => TRUE,
			static::ConfUserAllowLogin        => FALSE,
			static::ConfUserAllowSignup       => FALSE,
			static::ConfUserAllowSignupGank   => FALSE,
			static::ConfUserEmailActivate     => TRUE,
			static::ConfUserRequireAlias      => FALSE,
			static::ConfPageEnableDB          => FALSE,
			static::ConfPageEnableStatic      => TRUE,
			static::ConfPageStaticStorageKey  => 'Default',
			static::ConfPageStaticStoragePath => 'pages/static',
			static::ConfContactTo             => NULL,
			static::ConfContactBCC            => NULL,
			static::ConfContactSubject        => 'Contact from Website'
		]);

		return;
	}

	public function
	OnReady(... $Argv):
	void {

		$App = $Argv['App'];
		$Scan = NULL;

		////////

		$App->User = User\EntitySession::Get();

		if($App->User && $App->User->IsAdmin())
		$App->Log->InitAdminlog(
			$App->GetProjectRoot(),
			$App->Config[self::ConfLogFormat]
		);

		////////

		Atlantis\Media\TagLink::RegisterType(Atlantis\Media\FileTagLink::class);

		////////

		$Scan = new Atlantis\Util\LibraryRouteScanner($App);

		$Scan->AddPath(
			Common\Filesystem\Util::Pathify(dirname(__FILE__), 'Routes')
		);

		$Scan->Commit();

		return;
	}

	////////////////////////////////////////////////////////////////
	// DashboardSidebarInterface ///////////////////////////////////

	public function
	OnDashboardSidebar(Atlantis\Engine $App, Datastore $Sidebar):
	void {

		if(!$App->User)
		return;

		$Sidebar->Push(new Atlantis\Dashboard\AtlantisAccountSidebar);

		if($App->User->HasAccessTypeOrAdmin(static::AccessContactLogManage))
		$Sidebar->Push(new Atlantis\Dashboard\AtlantisContactLogSidebar);

		////////

		if($App->Config[Atlantis\Library::ConfPageEnableDB]) {
			if($App->User->HasAccessTypeOrAdmin(static::AccessPageManage))
			$Sidebar->Push(new Atlantis\Dashboard\AtlantisPageSidebar);
		}

		////////

		if($App->User->IsAdmin())
		$Sidebar
		->Push(new Atlantis\Dashboard\AtlantisAdminSidebar)
		->Push(new Atlantis\Dashboard\AtlantisMediaSidebar);

		return;
	}

	////////////////////////////////////////////////////////////////
	// AccessTypeDefInterface //////////////////////////////////////

	public function
	OnAccessTypeDefine(Atlantis\Engine $App, Common\Datastore $List):
	void {

		$List->MergeRight([
			new Atlantis\User\AccessTypeDef(
				static::AccessContactLogManage, 1,
				'Allow the user to view the Contact Us log.'
			),
			new Atlantis\User\AccessTypeDef(
				static::AccessPageManage, 1,
				'Allow the user to manage Pages on the site.'
			)
		]);

		return;
	}

}

