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
	Atlantis\Plugins\UploadHandlerInterface {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnLoad(...$Argv):
	void {

		if(!isset($Argv['App']))
		return;

		/** @var Atlantis\Engine $App */
		$App = $Argv['App'];

		////////

		static::$Config->BlendRight([
			Key::ConfLogFormat             => 'default',
			Key::ConfPassMinLen            => 10,
			Key::ConfPassReqAlphaLower     => TRUE,
			Key::ConfPassReqAlphaUpper     => TRUE,
			Key::ConfPassReqNumeric        => TRUE,
			Key::ConfPassReqSpecial        => TRUE,
			Key::ConfUserAllowLogin        => FALSE,
			Key::ConfUserAllowSignup       => FALSE,
			Key::ConfUserAllowSignupGank   => FALSE,
			Key::ConfUserEmailActivate     => TRUE,
			Key::ConfUserRequireAlias      => FALSE,
			Key::ConfPageEnableDB          => FALSE,
			Key::ConfPageEnableStatic      => TRUE,
			Key::ConfPageStaticStorageKey  => 'Default',
			Key::ConfPageStaticStoragePath => 'pages/static',
			Key::ConfUserAgent             => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
			Key::ConfContactTo             => NULL,
			Key::ConfContactBCC            => NULL,
			Key::ConfContactSubject        => 'Contact from Website',

			Key::ConfErrorDisplay          => NULL,
			Key::ConfErrorLogPath          => NULL,
			Key::ConfTrafficReporting      => TRUE,

			Key::PageTagIndexURL           => '/tags',
			Key::PageTagViewURL            => '/tag/:Alias:',

			// things found to be walking our websites while providing
			// absolutely no value back to the internet. these get
			// served blank pages, and are not logged by analytics.

			Key::ConfAccessIgnoreAgentHard => (
				// abusive bots who identify themselves.
				'AhrefsBot|Amazonbot|Barkrowler|BLEXBot|Bytespider|Bytedance|ClaudeBot|DataForSeoBot|DotBot|FriendlyCrawler|GoogleOther|GPTBot|IonCrawl|ImagesiftBot|MJ12bot|SemrushBot|serpstatbot|PetalBot|'
				// abusive bots who identify themselves poorly.
				. 'paloaltonetworks|linkfluence|internet\\-measurement|naver\\.me|'
				// abusive libraries used by people who cant be arsed to change the ua.
				. 'go-http-client'
			),

			// these tend to be things used by companies to fetch
			// thumbnails for links you send people in various chats and
			// messengers. additionally search engines people actually
			// use are allowed.

			Key::ConfAccessIgnoreAgentSoft => (''
				. 'Applebot|bingbot|facebookexternalhit|GoogleBot|Twitterbot|YandexBot'
			)
		]);

		////////

		($App->Plugins)
		->Register(Atlantis\Plugins\User\AccessTypeDefault::class)
		->Register(Atlantis\Plugins\Profile\AdminMenuDefault::class);

		($App->Plugins)
		->RegisterInterfacePlugin(
			Atlantis\Plugin\Interfaces\Engine\AppInstanceStaticInterface::class,
			Atlantis\Prototype::class
		);

		return;
	}

	public function
	OnReady(... $Argv):
	void {

		/** @var Atlantis\Engine $App */

		$App = $Argv['App'];
		$Scan = NULL;

		////////

		$App->User = User\EntitySession::Get();

		////////

		Atlantis\Media\FileTagLink::Register();
		Atlantis\Media\VideoThirdPartyTagLink::Register();
		Atlantis\Profile\EntityTagLink::Register();
		Atlantis\Tag\SubtagLink::Register();

		Atlantis\Struct\EntityRelationship::Register('Media.Image', Media\File::class);
		Atlantis\Struct\EntityRelationship::Register('Media.Video.ThirdParty', Media\VideoThirdParty::class);
		Atlantis\Struct\EntityRelationship::Register('Media.Related.Link', Media\RelatedLink::class);
		Atlantis\Struct\EntityRelationship::Register('Profile.Entity', Profile\Entity::class);

		////////

		$Scan = new Atlantis\Util\LibraryRouteScanner($App);

		$Scan->AddPath(
			Common\Filesystem\Util::Pathify(dirname(__FILE__), 'Routes')
		);

		$Scan->Commit();

		$this->OnReadyPluginAppInstanceStatic($App);

		return;
	}

	protected function
	OnReadyPluginAppInstanceStatic(Atlantis\Engine $App):
	void {

		($App->Plugins)
		->Get(Plugin\Interfaces\Engine\AppInstanceStaticInterface::class)
		->Each(fn(string $Class)=> ($Class)::AppInstanceSet($App));

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

		if($App->User->HasAccessTypeOrAdmin(Key::AccessContactLogManage))
		$Sidebar->Push(new Atlantis\Dashboard\AtlantisContactLogSidebar);

		////////

		if($App->Config[Key::ConfPageEnableDB]) {
			if($App->User->HasAccessTypeOrAdmin(Key::AccessPageManage))
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

		if($App->User->IsAdmin()) {

			if(!$App->Config->Get('TempDashboardNoTraffic'))
			$Elements
			->Push(new Atlantis\Dashboard\AtlantisTagsElement($App))
			->Push(new Atlantis\Dashboard\AtlantisTrafficElement($App));

		}

		$Elements
		->Push(new Atlantis\Dashboard\AtlantisAccountElement($App));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnUploadFinalise(Engine $App, string $UUID, string $Name, string $Type, Storage\File $File):
	void {

		$Type = $Type ?: 'default';

		switch($Type) {
			case 'tagcover':
				$this->OnUploadFinaliseTagCover($App, $UUID, $Name, $File);
			break;
			case 'tagphoto':
				$this->OnUploadFinaliseTagPhoto($App, $UUID, $Name, $File);
			break;
			default:
				$this->OnUploadFinaliseDefault($App, $UUID, $Name, $File);
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

