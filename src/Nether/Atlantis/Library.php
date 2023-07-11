<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Storage;
use Nether\User;

use Exception;
use Nether\Common\Datastore;

class Library
extends Common\Library
implements
	Atlantis\Plugins\DashboardSidebarInterface,
	Atlantis\Plugins\DashboardElementInterface,
	Atlantis\Plugins\AccessTypeDefineInterface,
	Atlantis\Plugins\UploadHandlerInterface {

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
	ConfProjectWebserver      = 'Project.WebServerType',
	ConfProjectWebCertType    = 'Project.WebCertType';

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
	ConfContactSubject      = 'Nether.Atlantis.Contact.Subject',
	ConfErrorDisplay        = 'Nether.Atlantis.Error.Display',
	ConfErrorLogPath        = 'Nether.Atlantis.Error.LogPath';

	const
	ConfPageEnableDB          = 'Nether.Atlantis.Page.EnableDatabase',
	ConfPageEnableStatic      = 'Nether.Atlantis.Page.EnableStatic',
	ConfPageStaticStorageKey  = 'Nether.Atlantis.Page.StaticStorageKey',
	ConfPageStaticStoragePath = 'Nether.Atlantis.Page.StaticStoragePath';

	const
	WebServerTypeNone     = NULL,
	WebServerTypeApache24 = 'apache24';

	const
	WebCertTypeAcmePHP = 'acmephp';

	const
	AccessContactLogManage = 'Nether.Atlantis.ContactLog.Manage',
	AccessPageManage       = 'Nether.Atlantis.Page.Manage';

	const
	PageTagIndexURL = 'Nether.Atlantis.Tag.PageIndexURL',
	PageTagViewURL = 'Nether.Atlantis.Tag.PageViewURL';

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
			static::ConfContactSubject        => 'Contact from Website',

			static::ConfErrorDisplay          => NULL,
			static::ConfErrorLogPath          => NULL,

			static::PageTagIndexURL           => '/tags',
			static::PageTagViewURL            => '/tag/:Alias:'
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

		Atlantis\Media\FileTagLink::RegisterType();

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

	public function
	OnDashboardElement(Atlantis\Engine $App, Datastore $Elements):
	void {

		if(!$App->User)
		return;

		if($App->User->IsAdmin())
		$Elements->Push(new Atlantis\Dashboard\AtlantisTagsElement($App));

		$Elements
		->Push(new Atlantis\Dashboard\AtlantisAccountElement($App))
		->Push(new Atlantis\Dashboard\AtlantisMediaElement($App));

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnUploadFinalise(Engine $App, string $UUID, string $Name, string $Type, Storage\File $File):
	void {

		$Type = $Type ?: 'default';

		switch($Type) {
			case 'default':
				$this->OnUploadFinaliseDefault($App, $UUID, $Name, $File);
			break;
			case 'tagcover':
				$this->OnUploadFinaliseTagCover($App, $UUID, $Name, $File);
			break;
			case 'tagphoto':
				$this->OnUploadFinaliseTagPhoto($App, $UUID, $Name, $File);
			break;
		}

		return;
	}

	protected function
	OnUploadFinaliseDefault(Engine $App, string $UUID, string $Name, Storage\File $File):
	void {

		$Storage = $App->Storage->Location('Default');

		$Path = sprintf(
			'upl/%s/original.%s',
			join('/', explode('-', $UUID, 2)),
			$File->GetExtension()
		);

		// move the file to where it needs to live.

		$Storage->Put($Path, $File->Read());
		$File->DeleteParentDirectory();

		// track the file in the database.

		$File = $Storage->GetFileObject($Path);

		$Entity = Atlantis\Media\File::Insert([
			'UUID'   => $UUID,
			'UserID' => $App->User?->ID,
			'Name'   => $Name,
			'Type'   => $File->GetType(),
			'Size'   => $File->GetSize(),
			'URL'    => $File->GetStorageURL()
		]);

		$Entity->GenerateExtraFiles();

		return;
	}

	protected function
	OnUploadFinaliseTagCover(Engine $App, string $UUID, string $Name, Storage\File $File):
	void {

		$Media = NULL;
		$Tag = NULL;
		$TagID = (int)$App->Router->Request->Data->Get('TagID');

		////////

		if(!$TagID)
		throw new Exception('field TagID is required for upload type "tagcover"');

		////////

		$this->OnUploadFinaliseDefault($App, $UUID, $Name, $File);

		$Media = Media\File::GetByUUID($UUID);

		if(!$Media)
		throw new Exception(sprintf('media not found %s', $UUID));

		////////

		$Tag = Tag\Entity::GetByID($TagID);

		if(!$Tag)
		throw new Exception(sprintf('tag not found %d', $TagID));

		////////

		$Tag->Update([ 'CoverImageID'=> $Media->ID ]);

		return;
	}

	protected function
	OnUploadFinaliseTagPhoto(Engine $App, string $UUID, string $Name, Storage\File $File):
	void {

		$Media = NULL;
		$Tag = NULL;
		$TagID = (int)$App->Router->Request->Data->Get('TagID');

		////////

		if(!$TagID)
		throw new Exception('field TagID is required for upload type "tagphoto"');

		////////

		$this->OnUploadFinaliseDefault($App, $UUID, $Name, $File);

		$Media = Media\File::GetByUUID($UUID);

		if(!$Media)
		throw new Exception(sprintf('media not found %s', $UUID));

		////////

		$Tag = Tag\Entity::GetByID($TagID);

		if(!$Tag)
		throw new Exception(sprintf('tag not found %d', $TagID));

		////////

		Tag\EntityPhoto::Insert([
			'EntityID' => $Tag->ID,
			'PhotoID'  => $Media->ID
		]);

		return;
	}


}

