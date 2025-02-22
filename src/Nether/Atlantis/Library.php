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
	Atlantis\Plugins\UploadHandlerInterface {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnLoad(...$Argv):
	void {

		/** @var Atlantis\Engine $App */
		$App = $Argv['App'];

		////////

		($this)
		->RegisterDefaultConfig($App)
		->RegisterPlugins($App)
		->RegisterForAppInst($App);

		return;
	}

	public function
	OnReady(... $Argv):
	void {

		/** @var Atlantis\Engine $App */
		$App = $Argv['App'];

		////////

		($this)
		->RegisterUserSession($App)
		->RegisterEntityLinks($App)
		->RegisterRoutes($App)
		->OnReadyPluginAppInstanceStatic($App);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	private function
	RegisterDefaultConfig(Engine $App):
	static {

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
				'AhrefsBot|Amazonbot|AwarioBot|Barkrowler|BLEXBot|Bytespider|Bytedance|ClaudeBot|DataForSeoBot|DotBot|FriendlyCrawler|GoogleOther|GPTBot|IonCrawl|ImagesiftBot|MJ12bot|SemrushBot|serpstatbot|PetalBot|'
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

		return $this;
	}

	private function
	RegisterPlugins(Engine $App):
	static {

		($App->Plugins)
		->Register(Atlantis\Plugins\User\AccessTypeDefault::class)
		->Register(Atlantis\Plugins\Profile\AdminMenuDefault::class)
		->Register(Atlantis\Plugins\Dashboard\ContentInfoWidget::class)
		->Register(Atlantis\Plugins\Dashboard\TrafficInfoWidget::class)
		->Register(Atlantis\Plugins\Dashboard\UserInfoWidget::class)
		->Register(Atlantis\Plugins\Dashboard\SystemInfoWidget::class);

		return $this;
	}

	private function
	RegisterForAppInst(Engine $App):
	static {

		($App->Plugins)
		->Register(Atlantis\Prototype::class)
		->Register(Atlantis\Util::class);

		return $this;
	}

	private function
	RegisterUserSession(Engine $App):
	static {

		$App->User = User\EntitySession::Get();

		return $this;
	}

	private function
	RegisterEntityLinks(Engine $App):
	static {

		Atlantis\Media\FileTagLink::Register();
		Atlantis\Media\VideoThirdPartyTagLink::Register();
		Atlantis\Profile\EntityTagLink::Register();
		Atlantis\Tag\SubtagLink::Register();

		Atlantis\Struct\EntityRelationship::Register(Media\File::EntType, Media\File::class);
		Atlantis\Struct\EntityRelationship::Register('Media.Video.ThirdParty', Media\VideoThirdParty::class);
		Atlantis\Struct\EntityRelationship::Register('Media.Related.Link', Media\RelatedLink::class);
		Atlantis\Struct\EntityRelationship::Register('Profile.Entity', Profile\Entity::class);
		Atlantis\Struct\EntityRelationship::Register(Atlantis\Tag\Entity::EntType, Atlantis\Tag\Entity::class);

		return $this;
	}

	private function
	RegisterRoutes(Engine $App):
	static {

		$Scan = new Atlantis\Util\LibraryRouteScanner($App);

		$Scan->AddPath(Common\Filesystem\Util::Pathify(
			dirname(__FILE__), 'Routes'
		));

		$Scan->Commit();

		return $this;
	}

	private function
	OnReadyPluginAppInstanceStatic(Atlantis\Engine $App):
	void {

		($App->Plugins)
		->Get(Plugin\Interfaces\Engine\AppInstanceStaticInterface::class)
		->Each(fn(string $Class)=> ($Class)::AppInstanceSet($App));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Deprecated('2024-06-24', 'migrate to FileUploadAPI.')]
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
			case 'default':
				$this->OnUploadFinaliseDefault($App, $UUID, $Name, $File);
			break;
		}

		return;
	}

	#[Common\Meta\Deprecated('2024-06-24', 'migrate to FileUploadAPI.')]
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

	#[Common\Meta\Deprecated('2024-06-24', 'migrate to FileUploadAPI.')]
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

	#[Common\Meta\Deprecated('2024-06-24', 'migrate to FileUploadAPI.')]
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

