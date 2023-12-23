<?php

namespace Nether\Atlantis\Routes\Profile;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Blog;
use Nether\Common;

use Exception;
use Nether\Atlantis\Struct\EntityRelationship;

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

			$News = Blog\Post::Find([
				'UUID' => $NewsItems->GetData(),
				'Sort' => 'newest'
			]);
		}

		////////

		($this->Surface)
		->Set('Page.Title', $Profile->Title)
		->Set('Page.ImageURL', $Profile->GetCoverImageURL('lg'))
		->Area($this->GetViewArea(), [
			'Profile' => $Profile,
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

}
