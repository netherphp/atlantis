<?php

namespace Nether\Atlantis\Routes\Profile;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

use Exception;

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

		////////

		($this->Surface)
		->Set('Page.Title', $Profile->Title)
		->Area($this->GetViewArea(), [
			'Profile' => $Profile,
			'Tags'    => $Tags,
			'Photos'  => $Photos,
			'Videos'  => $Videos,
			'Links'   => $Links
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
