<?php

namespace Nether\Atlantis\Routes\Profile;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

use Exception;

class ProfileWeb
extends Atlantis\PublicWeb {

	#[Avenue\Meta\RouteHandler('/::Alias::', Verb: 'GET')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(string $Alias, Atlantis\Profile\Entity $Profile):
	void {

		($this->Surface)
		->Set('Page.Title', $Profile->Title);

		////////

		$this->Surface->Area(
			$this->GetViewArea(),
			[ 'Profile'=> $Profile ]
		);

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

	protected function
	GetViewArea():
	string {

		return 'profile/view';
	}

}
