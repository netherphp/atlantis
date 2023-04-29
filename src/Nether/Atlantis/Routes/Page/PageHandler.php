<?php

namespace Nether\Atlantis\Routes\Page;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class PageHandler
extends Atlantis\PublicWeb {

	#[Avenue\Meta\RouteHandler('/::Alias::', Verb: 'GET')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(string $Alias, Atlantis\Page\Entity $Page):
	void {

		($this->Surface)
		->Set('Page.Title', $Page->Title);

		////////

		if($Page->Editor === 'static') {
			echo $Page->Content;
			return;
		}

		////////

		$this->Surface->Wrap(
			$this->GetViewArea(),
			[ 'Page'=> $Page ]
		);

		return;
	}

	protected function
	ViewWillAnswerRequest(string $Alias, Avenue\Struct\ExtraData $Data):
	int {

		$Data['Page'] = NULL;

		////////

		if($this->App->Config[Atlantis\Library::ConfPageEnableDB]) {
			$Data['Page'] = Atlantis\Page\Entity::GetByField(
				'Alias',
				$Alias
			);

			if($Data['Page'])
			return Avenue\Response::CodeOK;
		}

		if($this->App->Config[Atlantis\Library::ConfPageEnableStatic]) {
			$Data['Page'] = Atlantis\Page\Entity::FromStaticFile(
				$this->App,
				$Alias
			);

			if($Data['Page'])
			return Avenue\Response::CodeOK;
		}

		////////

		return Avenue\Response::CodeNope;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetViewArea():
	string {

		return 'page/view';
	}

}
