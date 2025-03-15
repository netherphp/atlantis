<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugins\Profile;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class AdminMenuDefault
extends Atlantis\Plugin
implements Atlantis\Plugin\Interfaces\ProfileView\AdminMenuSectionInterface {

	const
	SectionEditing = 'editing',
	SectionTagging = 'tagging',
	SectionMedia   = 'media',
	SectionDanger  = 'danger';

	const
	EditTitle           = 'ProfileEditMenuTitle',
	EditItemTitle       = 'ProfileEditItemTitleAlias',
	EditItemDetails     = 'ProfileEditItemDetails',
	EditItemAddress     = 'ProfileEditItemAddress',
	EditItemSocial      = 'ProfileEditItemSocialMedia',
	EditItemAdminNotes  = 'ProfileEditItemAdminNotes';

	const
	TaggingTitle         = 'ProfileTaggingMenuTitle',
	TaggingItemTags      = 'ProfileTaggingEdit',
	TaggingItemRelations = 'ProfileTaggingERLink';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetItemsForSection(Atlantis\Profile\Entity $Profile, string $Key, Common\Datastore $ExtraData):
	?Common\Datastore {

		return match($Key) {
			static::SectionEditing
			=> $this->GetItemsForEditing($Profile, $ExtraData),

			static::SectionTagging
			=> $this->GetItemsForTagging($Profile, $ExtraData),

			static::SectionMedia
			=> $this->GetItemsForMedia($Profile, $ExtraData),

			static::SectionDanger
			=> $this->GetItemsForDangerZone($Profile, $ExtraData),

			default
			=> NULL
		};
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetItemsForEditing(Atlantis\Profile\Entity $Profile, Common\Datastore $ExtraData):
	Common\Datastore {

		$Output = new Common\Datastore;

		////////

		($Output)
		->Shove(static::EditTitle, Atlantis\Struct\DropdownItem::New(
			Title: '# Editing'
		))
		->Shove(static::EditItemTitle, Atlantis\Struct\DropdownItem::New(
			Title: 'Title & Alias',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'title' ], TRUE)
		))
		->Shove(static::EditItemDetails, Atlantis\Struct\DropdownItem::New(
			Title: 'Description',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'details' ], TRUE)
		))
		->Shove(static::EditItemAddress, Atlantis\Struct\DropdownItem::New(
			Title: 'Address & Contact',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'address' ], TRUE)
		))
		->Shove(static::EditItemSocial, Atlantis\Struct\DropdownItem::New(
			Title: 'Web Links',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'links' ], TRUE)
		));

		if($this->App->User && $this->App->User->IsAdmin())
		$Output->Shove(static::EditItemAdminNotes, Atlantis\Struct\DropdownItem::New(
			Title: 'Admin Notes',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'admin-notes' ], TRUE)
		));

		////////

		return $Output;
	}

	protected function
	GetItemsForTagging(Atlantis\Profile\Entity $Profile, Common\Datastore $ExtraData):
	Common\Datastore {

		$Output = new Common\Datastore;

		////////

		($Output)
		->Shove(static::TaggingTitle, Atlantis\Struct\DropdownItem::New(
			Title: '# Tagging'
		))
		->Shove(static::TaggingItemTags, Atlantis\Struct\DropdownItem::New(
			Title: 'Tags',
			Icon: 'mdi-tag-multiple',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'tags' ], TRUE)
		))
		->Shove(static::TaggingItemRelations, Atlantis\Struct\DropdownItem::New(
			Title: 'Related Profiles',
			Icon: 'mdi-text-box-multiple-outline',
			Attr: $Profile->GetDataAttr([
				'profile-cmd' => 'erlink',
				'er-type'     => Atlantis\Profile\Entity::EntType,
				'er-title'    => 'Related Profiles'
			], TRUE)
		));

		////////

		return $Output;
	}

	protected function
	GetItemsForMedia(Atlantis\Profile\Entity $Profile, Common\Datastore $ExtraData):
	Common\Datastore {

		$Output = new Common\Datastore;

		////////

		($Output)
		->Shove('ProfileMediaMenuTitle', Atlantis\Struct\DropdownItem::New(
			Title: '# Media'
		))
		->Shove('ProfileMediaUploadPhoto', Atlantis\Struct\DropdownItem::New(
			Title: 'Upload Photos',
			Icon: 'mdi-upload',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'photo' ], TRUE)
		))
		->Shove('ProfileMediaAddVideoURL', Atlantis\Struct\DropdownItem::New(
			Title: 'Add Video',
			Icon: 'mdi-video-plus',
			Attr: $Profile->GetDataAttr([ 'videotp-cmd' => 'new2', 'other-type'=> $Profile::EntType, 'other-uuid'=> $Profile->UUID ], TRUE)
		))
		->Shove('ProfileMediaAddRelatedLink', Atlantis\Struct\DropdownItem::New(
			Title: 'Add Related Link',
			Icon: 'mdi-link-plus',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'related-link' ], TRUE)
		));

		////////

		return $Output;
	}

	protected function
	GetItemsForDangerZone(Atlantis\Profile\Entity $Profile, Common\Datastore $ExtraData):
	Common\Datastore {

		$Output = new Common\Datastore;

		////////

		if(!$Profile->Enabled)
		$Output->Shove('ProfileStateEnable', Atlantis\Struct\DropdownItem::New(
			Title: 'Enable Profile',
			Icon: 'mdi-eye',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'enable' ], TRUE)
		));

		else
		$Output->Shove('ProfileStateDisable', Atlantis\Struct\DropdownItem::New(
			Title: 'Disable Profile',
			Icon: 'mdi-eye-off',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'disable' ], TRUE)
		));

		////////

		($Output)
		->Shove('ProfileStateDelete', Atlantis\Struct\DropdownItem::New(
			Title: 'Delete',
			Icon: 'mdi-trash-can',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'delete' ], TRUE),
			Warn: Atlantis\Struct\DropdownItem::Danger
		));

		////////

		return $Output;
	}

};
