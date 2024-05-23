<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Avenue;
use Nether\Blog;

class VideoWeb
extends Atlantis\ProtectedWeb {

	#[Avenue\Meta\RouteHandler('/video/:VideoID:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(int $VideoID, Atlantis\Media\VideoThirdParty $Video):
	void {

		$Links = $Video->Profile->FetchRelatedLinks();
		$Related = $Video->Profile->FetchRelatedProfiles();

		////////

		$Profiles = NULL;
		$Posts = NULL;

		////////

		$ProfileIndex = Atlantis\Struct\EntityRelationship::Find([
			'ParentUUID' => $Video->UUID,
			'ChildType'  => 'Profile.Entity',
			'Remappers'  => [
				Atlantis\Struct\EntityRelationship::MapToChildUUID(...)
			]
		]);

		if($ProfileIndex->Count())
		$Profiles = Atlantis\Profile\Entity::Find([
			'UseSiteTags' => FALSE,
			'UUID'        => $ProfileIndex->Export()
		]);

		//Common\Dump::Var($ProfileIndex->Export(), TRUE);
		//Common\Dump::Var($Profiles->Export(), TRUE);

		////////

		$PostIndex = Atlantis\Struct\EntityRelationship::Find([
			'ParentType' => 'Blog.Post',
			'ChildUUID'  => $Video->UUID
		]);

		$PostIndex->Remap(fn($I)=> $I->ParentUUID);

		if($PostIndex->Count())
		$Posts = Blog\Post::Find([ 'UUID'=> $PostIndex->GetData() ]);

		////////

		$this->Surface
		->Set('Page.Title', sprintf('Video: %s', $Video->Title))
		->Wrap('media/video/view', [
			'Video'    => $Video,
			'Related'  => $Related,
			'Links'    => $Links,
			'Tags'     => $Video->GetTags(),
			'News'     => $Posts,
			'Profiles' => $Profiles
		]);

		return;
	}

	public function
	ViewWillAnswerRequest(int $VideoID, Avenue\Struct\ExtraData $Data):
	int {

		$Video = Atlantis\Media\VideoThirdParty::GetBYID($VideoID);

		if(!$Video)
		return ($this->Response)::CodeNope;

		////////

		$Data['Video'] = $Video;
		return ($this->Response)::CodeOK;
	}

}
