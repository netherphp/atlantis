<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Avenue;
use Nether\Blog;

class ProfileFileWeb
extends Atlantis\ProtectedWeb {

	#[Avenue\Meta\RouteHandler('/file/:FileID:')]
	#[Avenue\Meta\RouteHandler('/photo/:FileID:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	#[Avenue\Meta\ExtraDataArgs]
	public function
	View(int $FileID, Atlantis\Media\File $File):
	void {

		if($File->Type === $File::TypeImg)
		$this->ViewFileImage($File);

		else
		$this->Quit(404);

		return;
	}

	public function
	ViewWillAnswerRequest(int $FileID, Avenue\Struct\ExtraData $Data):
	int {

		$File = Atlantis\Media\File::GetByID($FileID);

		////////

		if(!$File)
		return ($this->Response)::CodeNope;

		////////

		$Data['File'] = $File;

		return ($this->Response)::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	ViewFileGeneric(Atlantis\Media\File $File):
	void {

		return;
	}

	protected function
	ViewFileImage(Atlantis\Media\File $File):
	void {

		$Scope = [
			'File'     => $File,
			'Related'  => $this->FetchRelatedProfiles($File),
			'Tags'     => $File->Profile->GetTags()
		];

		$File->BootProfile();

		////////

		($this->Surface)
		->Area('media/image/view', $Scope);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchFileProfile(Atlantis\Media\File $File):
	Atlantis\Profile\Entity {

		$File->BootProfile();

		return $File->Profile;
	}

	protected function
	FetchRelatedProfiles(Atlantis\Media\File $File):
	?Database\ResultSet {

		$Profiles = NULL;

		////////

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID' => $File->UUID,
			'EntityType' => Atlantis\Profile\Entity::EntType,
			'Remappers'  => [
				fn(Atlantis\Struct\EntityRelationship $ERI)
				=> Atlantis\Struct\EntityRelationship::KeepTheOtherUUID(
					$ERI, $File->UUID
				)
			]
		]);

		if($Index->Count())
		$Profiles = Atlantis\Profile\Entity::Find([
			'UseSiteTags' => FALSE,
			'Enabled'     => NULL,
			'UUID'        => $Index->Export()
		]);

		////////

		return $Profiles;
	}

	protected function
	FetchRelatedNews(Atlantis\Media\VideoThirdParty $Video):
	?Database\ResultSet {

		$Posts = NULL;

		////////

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID' => $Video->UUID,
			'EntityType' => Blog\Post::EntType,
			'Remappers'  => [
				fn(Atlantis\Struct\EntityRelationship $ERI)
				=> Atlantis\Struct\EntityRelationship::KeepTheOtherUUID(
					$ERI, $Video->UUID
				)
			]
		]);

		if($Index->Count())
		$Posts = Blog\Post::Find([
			'UseSiteTags' => FALSE,
			'UUID'        => $Index->GetData()
		]);

		////////

		return $Posts;
	}

}
