<?php

namespace Nether\Atlantis\Routes\Profile;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Blog;
use Nether\Common;

use Nether\Atlantis\Plugin\Interfaces;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Exception;
use Nether\Atlantis\Struct\EntityRelationship;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\AdminMenuBeforeInterface;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\AdminMenuAfterInterface;
use Nether\Atlantis\Plugin\Interfaces\ProfileView\AdminMenuSectionInterface;
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
		$News = NULL;

		$SectionsBefore = static::ProfileViewExtraSectionsBefore(
			$this->App, $Profile
		);

		$SectionsAfter = static::ProfileViewExtraSectionsAfter(
			$this->App, $Profile
		);

		$ExtraData = static::ProfileViewExtraData(
			$this->App, $Profile
		);

		$AdminMenu = static::ProfileViewAdminMenu($this->App, $Profile);

		////////

		if(class_exists('Nether\\Blog\\Library')) {
			$NewsItems = EntityRelationship::Find([
				'EntityUUID' => $Profile->UUID,
				'EntityType' => 'Blog.Post',
				'Remappers'  => [
					fn(EntityRelationship $I)
					=> EntityRelationship::KeepTheOtherOne($I, $Profile->UUID)
				]
			]);

			//$News = Blog\Post::Find([
			//	'UUID' => $NewsItems->GetData(),
			//	'Sort' => 'newest'
			//]);
		}

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
	ProfileViewAdminMenu(Atlantis\Engine $App, Atlantis\Profile\Entity $Profile):
	Atlantis\Struct\DropdownMenu {

		$AdminMenu = Atlantis\Struct\DropdownMenu::New();

		if(!$App->User || !$App->User->IsAdmin())
		return $AdminMenu;

		////////

		$Plugins = $App->Plugins->GetInstanced(AdminMenuSectionInterface::class);

		$Sections = Common\Datastore::FromArray([
			'before'  => NULL,
			'editing' => NULL,
			'tagging' => NULL,
			'media'   => NULL,
			'danger'  => NULL,
			'after'   => NULL
		]);

		// have the plugins prepare their button lists and bake them into
		// the final admin menu button.

		($Sections)
		->RemapKeyValue(function(string $Key) use($Plugins, $Profile) {
			return $Plugins->Compile(
				fn(Common\Datastore $C, AdminMenuSectionInterface $S)
				=> $C->MergeRight($S->GetItemsForSection( $Profile, $Key ) ?? [])
			);
		})
		->EachKeyValue(function(string $Key, Common\Datastore $Items) use($AdminMenu) {

			if(!$Items->Count())
			return;

			////////

			if($Key === 'danger')
			$AdminMenu->Items->Push(Atlantis\Struct\DropdownItem::New(Title: '-'));
			else
			$AdminMenu->Items->Push(Atlantis\Struct\DropdownItem::New(Title: '~'));

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

		$Plugins = $App->Plugins->GetInstanced(
			Interfaces\ProfileView\ExtraDataInterface::class
		);

		$Output = $Plugins->Accumulate(new Common\Datastore, (
			fn(Common\Datastore $C, Interfaces\ProfileView\ExtraDataInterface $P)
			=> $C->MergeRight($P->GetExtraData( $Profile ))
		));

		return $Output;
	}

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
