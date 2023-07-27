<?php

namespace Nether\Atlantis\Routes\Profile;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

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

		$Data['Profile'] = Atlantis\Profile\Entity::GetByField(
			'Alias', $Alias
		);

		if($Data['Profile'])
		return ($this->Response)::CodeOK;

		////////

		return Avenue\Response::CodeNope;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetViewArea():
	string {

		return 'profile/view';
	}

}
