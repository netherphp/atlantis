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

		$Plugins = (
			($App->Plugins)
			->Get(Atlantis\Plugins\PrototypeAdminMenuInterface::class)
			->Remap(fn(string $C)=> new $C($App))
			->Accumulate(new Common\Datastore, (
				fn(Common\Datastore $C, Atlantis\Plugins\PrototypeAdminMenuInterface $P)
				=> $C->MergeRight($P->GetItems( $Profile ))
			))
			->Sort()
		);

		($AdminMenu)
		->ItemNew('Manage Tags', 'mdi-tag-multiple', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'tags' ], TRUE))
		->ItemNew('Edit Title & Alias', 'mdi-pencil', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'title' ], TRUE))
		->ItemNew('Edit Description', 'mdi-pencil', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'details' ], TRUE))
		->ItemNew('Edit Street Address', 'mdi-pencil', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'address' ], TRUE))
		->ItemNew('Edit Web Links', 'mdi-pencil', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'links' ], TRUE))
		->ItemNew('Enable Profile', 'mdi-eye', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'enable' ], TRUE), If: !$Profile->Enabled)
		->ItemNew('Disable Profile', 'mdi-eye-off', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'disable' ], TRUE), If: $Profile->Enabled)
		->ItemNew('-');

		if($Plugins && $Plugins->Count()) {
			$AdminMenu->ItemPush($Plugins);
			$AdminMenu->ItemNew('-');
		}

		($AdminMenu)
		->ItemNew('Upload Photos', 'mdi-upload', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'photo' ], TRUE))
		->ItemNew('Add Related Link', 'mdi-web', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'related-link' ], TRUE))
		->ItemNew('Add Video URL', 'mdi-video', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'videotp' ], TRUE))
		->ItemNew('-');

		($AdminMenu)
		->ItemNew('Delete', 'mdi-delete', Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'delete' ], TRUE));

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
			Interfaces\ProfileView\ExtraSectionsBeforeInterface::class
		);

		$Output = $Plugins->Accumulate(new Common\Datastore, (
			fn(Common\Datastore $C, Interfaces\ProfileView\ExtraSectionsBeforeInterface $P)
			=> $C->MergeRight($P->GetExtraSectionsBefore( $Profile ))
		));

		return $Output;
	}

	#[Common\Meta\Date('2023-12-27')]
	static public function
	ProfileViewExtraSectionsAfter(Atlantis\Engine $App, Atlantis\Profile\Entity $Profile):
	Common\Datastore {

		$Plugins = $App->Plugins->GetInstanced(
			Interfaces\ProfileView\ExtraSectionAfterInterface::class
		);

		$Output = $Plugins->Accumulate(new Common\Datastore, (
			fn(Common\Datastore $C, Interfaces\ProfileView\ExtraSectionsAfterInterface $P)
			=> $C->MergeRight($P->GetExtraSectionsAfter( $Profile ))
		));

		return $Output;
	}

}
