<?php

namespace Nether\Atlantis\Routes\Profile;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Exception;
use Nether\Atlantis\Struct\EntityRelationship;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\AdminMenuSectionInterface;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\AdminMenuAuditInterface;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\ExtraDataInterface;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\ExtraSectionsBeforeInterface;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\ExtraSectionsAfterInterface;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ProfileWeb
extends Atlantis\PublicWeb {

	#[Avenue\Meta\RouteHandler('/profile/::Alias::', Verb: 'GET')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(string $Alias, Atlantis\Profile\Entity $Profile):
	void {

		$Tags = $Profile->GetTags();
		$Photos = $Profile->FetchPhotos();
		$Videos = $Profile->FetchVideos();
		$Links = $Profile->FetchRelatedLinks();
		$Related = $Profile->FetchRelatedProfiles();
		$News = $Profile->FetchNews();

		$SectionsBefore = static::ProfileViewExtraSectionsBefore(
			$this->App, $Profile
		);

		$SectionsAfter = static::ProfileViewExtraSectionsAfter(
			$this->App, $Profile
		);

		$ExtraData = static::ProfileViewExtraData(
			$this->App, $Profile
		);

		$AdminMenu = static::ProfileViewAdminMenu(
			$this->App, $Profile, $ExtraData
		);

		////////

		($this->Surface)
		->Set('Page.Title', $Profile->Title)
		->Set('Page.ImageURL', $Profile->GetCoverImageURL('lg'))
		->Area($this->GetViewArea(), [
			'Profile'        => $Profile,
			'AdminMenu'      => $AdminMenu,
			'SectionsBefore' => $SectionsBefore,
			'SectionsAfter'  => $SectionsAfter,
			'ExtraData'      => $ExtraData,

			'Tags'    => $Tags,
			'Photos'  => $Photos,
			'Videos'  => $Videos,
			'Links'   => $Links,
			'Related' => $Related,
			'News'    => $News
		]);

		return;
	}

	protected function
	ViewWillAnswerRequest(string $Alias, Avenue\Struct\ExtraData $Data):
	int {

		$Data['Profile'] = NULL;

		////////

		try {
			$Data['Profile'] = Atlantis\Profile\Entity::GetByField(
				'Alias', $Alias
			);
		}

		catch(Exception $Err) {
			return ($this->Response)::CodeNope;
		}

		if(!$Data['Profile'])
		return ($this->Response)::CodeNope;

		////////

		return ($this->Response)::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetViewArea():
	string {

		return 'profile/view';
	}

	public function
	GetTagURL():
	string {

		return '/tag/:Alias:';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-12-26')]
	#[Common\Meta\Info('Allow plugins add things to the Profile Admin Menu.')]
	static public function
	ProfileViewAdminMenu(Atlantis\Engine $App, Atlantis\Profile\Entity $Profile, Common\Datastore $ExtraData):
	Atlantis\Struct\DropdownMenu {

		$AdminMenu = Atlantis\Struct\DropdownMenu::New();

		if(!$App->User || !$App->User->IsAdmin())
		return $AdminMenu;

		////////

		$Plugins = $App->Plugins->GetInstanced(AdminMenuSectionInterface::class);
		$Audits = $App->Plugins->GetInstanced(AdminMenuAuditInterface::class);

		$Sections = Common\Datastore::FromArray([
			'before'  => NULL,
			'editing' => NULL,
			'tagging' => NULL,
			'media'   => NULL,
			'danger'  => NULL,
			'after'   => NULL
		]);

		// have the plugins prepare their button lists merging them all down
		// into one list. plugins loaded later can override plugins loaded
		// earlier if the aliases collide. this is on purpose.

		$Sections->RemapKeyValue(function(string $Key) use($Profile, $Plugins, $ExtraData) {
			return $Plugins->Compile(
				fn(Common\Datastore $C, AdminMenuSectionInterface $S)
				=> $C->MergeRight($S->GetItemsForSection( $Profile, $Key, $ExtraData ) ?? [])
			);
		});

		// allow plugins to audit menu items in case they wanted to replace
		// or remove something.

		$Audits->Each(function(AdminMenuAuditInterface $Audit) use($Profile, $Sections, $ExtraData) {
			$Audit->AuditItems($Profile, $Sections, $ExtraData);
			return;
		});

		// cook the buttons into the admin menu.

		$Sections->EachKeyValue(function(string $Key, Common\Datastore $Items) use($AdminMenu) {

			if(!$Items->Count())
			return;

			////////

			if($Key === 'danger') {
				$AdminMenu->Items->Push(Atlantis\Struct\DropdownItem::New(Title: '~'));
				$AdminMenu->Items->Push(Atlantis\Struct\DropdownItem::New(Title: '-'));
			}

			else {
				$AdminMenu->Items->Push(Atlantis\Struct\DropdownItem::New(Title: '~'));
			}

			////////

			$AdminMenu->Items->MergeRight($Items);

			return;
		});

		////////

		return $AdminMenu;
	}

	#[Common\Meta\Date('2023-12-26')]
	#[Common\Meta\Info('Allow plugins fill a Datastore with additional info custom templates might need.')]
	static public function
	ProfileViewExtraData(Atlantis\Engine $App, Atlantis\Profile\Entity $Profile):
	Common\Datastore {

		$Plugins = $App->Plugins->GetInstanced(ExtraDataInterface::class);

		$Output = $Plugins->Accumulate(new Common\Datastore, (
			fn(Common\Datastore $C, ExtraDataInterface $P)
			=> $C->MergeRight($P->GetExtraData( $Profile ))
		));

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// these need to be restructured to work more like the admin menu where
	// it generates a section list that can be audited later.

	#[Common\Meta\Date('2023-12-27')]
	static public function
	ProfileViewExtraSectionsBefore(Atlantis\Engine $App, Atlantis\Profile\Entity $Profile):
	Common\Datastore {

		$Plugins = $App->Plugins->GetInstanced(
			ExtraSectionsBeforeInterface::class
		);

		$Output = $Plugins->Accumulate(new Common\Datastore, (
			fn(Common\Datastore $C, ExtraSectionsBeforeInterface $P)
			=> $C->MergeRight($P->GetExtraSectionsBefore( $Profile ))
		));

		return $Output;
	}

	#[Common\Meta\Date('2023-12-27')]
	static public function
	ProfileViewExtraSectionsAfter(Atlantis\Engine $App, Atlantis\Profile\Entity $Profile):
	Common\Datastore {

		$Plugins = $App->Plugins->GetInstanced(
			ExtraSectionsAfterInterface::class
		);

		$Output = $Plugins->Accumulate(new Common\Datastore, (
			fn(Common\Datastore $C, ExtraSectionsAfterInterface $P)
			=> $C->MergeRight($P->GetExtraSectionsAfter( $Profile ))
		));

		return $Output;
	}

}
