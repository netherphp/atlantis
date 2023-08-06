<?php

namespace Nether\Atlantis\Routes\Page;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class PageWeb
extends Atlantis\PublicWeb {

	#[Avenue\Meta\RouteHandler('/::Alias::', Verb: 'GET')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(string $Alias, Atlantis\Page\Entity $Page):
	void {

		($this->Surface)
		->Set('Page.Title', $Page->Title);

		if(!$Page->HasContent())
		$Page->Render($this->App);

		////////

		$this->Surface->Area(
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

		if($this->App->Config[Atlantis\Key::ConfPageEnableStatic]) {
			$Data['Page'] = Atlantis\Page\Entity::FromStaticFile(
				$this->App,
				$Alias
			);

			if($Data['Page'])
			return Avenue\Response::CodeOK;
		}

		if($this->App->Config[Atlantis\Key::ConfPageEnableDB]) {
			$Data['Page'] = Atlantis\Page\Entity::GetByField(
				'Alias',
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
