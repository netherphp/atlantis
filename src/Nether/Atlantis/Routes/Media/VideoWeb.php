<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Avenue;
use Nether\Blog;

class VideoWeb
extends Atlantis\ProtectedWeb {

	#[Avenue\Meta\RouteHandler('/video/:VideoID:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(int $VideoID, Atlantis\Media\VideoThirdParty $Video):
	void {

		$Profiles = $this->FetchRelatedProfiles($Video);
		$Posts = $this->FetchRelatedNews($Video);
		$Links = $Video->Profile->FetchRelatedLinks();

		////////

		$this->Surface
		->Set('Page.Title', sprintf('Video: %s', $Video->Title))
		->Wrap('media/video/view', [
			'Video'    => $Video,
			'Links'    => $Links,
			'Tags'     => $Video->GetTags(),
			'News'     => $Posts,
			'Related'  => $Profiles
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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchRelatedProfiles(Atlantis\Media\VideoThirdParty $Video):
	?Database\ResultSet {

		$Profiles = NULL;

		////////

		$Index = Atlantis\Struct\EntityRelationship::Find([
			'EntityUUID' => $Video->UUID,
			'EntityType' => Atlantis\Profile\Entity::EntType,
			'Remappers'  => [
				fn(Atlantis\Struct\EntityRelationship $ERI)
				=> Atlantis\Struct\EntityRelationship::KeepTheOtherOne(
					$ERI, $Video->UUID, FALSE
				)
			]
		]);

		if($Index->Count())
		$Profiles = Atlantis\Profile\Entity::Find([
			'UseSiteTags' => FALSE,
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
				=> Atlantis\Struct\EntityRelationship::KeepTheOtherOne(
					$ERI, $Video->UUID, FALSE
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
