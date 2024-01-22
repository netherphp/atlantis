<?php

////////////////////////////////////////////////////////////////////////////////
namespace Nether\Atlantis\Plugins\Profile; /////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class AdminMenuDefault
extends Atlantis\Plugin
implements Atlantis\Plugin\Interfaces\ProfileView\AdminMenuSectionInterface {

	public function
	GetItemsForSection(Atlantis\Profile\Entity $Profile, string $Key, Common\Datastore $ExtraData):
	?Common\Datastore {

		return match($Key) {
			'editing'
			=> $this->GetItemsForEditing($Profile, $ExtraData),

			'tagging'
			=> $this->GetItemsForTagging($Profile, $ExtraData),

			'media'
			=> $this->GetItemsForMedia($Profile, $ExtraData),

			'danger'
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
		->Shove('ProfileEditMenuTitle', Atlantis\Struct\DropdownItem::New(
			Title: '# Editing'
		))
		->Shove('ProfileEditTitleAlias', Atlantis\Struct\DropdownItem::New(
			Title: 'Title & Alias',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'title' ], TRUE)
		))
		->Shove('ProfileEditDetails', Atlantis\Struct\DropdownItem::New(
			Title: 'Description',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'details' ], TRUE)
		))
		->Shove('ProfileEditAddress', Atlantis\Struct\DropdownItem::New(
			Title: 'Addresss',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'address' ], TRUE)
		))
		->Shove('ProfileEditLinks', Atlantis\Struct\DropdownItem::New(
			Title: 'Social Media',
			Icon: 'mdi-pencil',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'links' ], TRUE)
		));

		if(!$Profile->Enabled)
		$Output->Shove('ProfileEditEnable', Atlantis\Struct\DropdownItem::New(
			Title: 'Enable Profile',
			Icon: 'mdi-eye',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'enable' ], TRUE)
		));

		else
		$Output->Shove('ProfileEditDisable', Atlantis\Struct\DropdownItem::New(
			Title: 'Disable Profile',
			Icon: 'mdi-eye-off',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'disable' ], TRUE)
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
		->Shove('ProfileTaggingMenuTitle', Atlantis\Struct\DropdownItem::New(
			Title: '# Tagging'
		))
		->Shove('ProfileTaggingEdit', Atlantis\Struct\DropdownItem::New(
			Title: 'Tags',
			Icon: 'mdi-tag-multiple',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'tags' ], TRUE)
		))
		->Shove('ProfileTaggingERLink', Atlantis\Struct\DropdownItem::New(
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
			Title: 'Add Video URL',
			Icon: 'mdi-video-plus',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'videotp' ], TRUE)
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

		($Output)
		->Shove('ProfileDangerDelete', Atlantis\Struct\DropdownItem::New(
			Title: 'Delete',
			Icon: 'mdi-trash-can',
			Attr: $Profile->GetDataAttr([ 'profile-cmd' => 'delete' ], TRUE)
		));

		////////

		return $Output;
	}

};
