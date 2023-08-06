<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Email;
use Nether\User;

class PageWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/page/list')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	HandleList():
	void {

		$Pages = Atlantis\Page\Entity::Find([
			'Page'  => 1,
			'Limit' => 0
		]);

		($this->Surface)
		->Wrap('dashboard/page/index', [
			'Pages' => $Pages
		]);

		return;
	}

	protected function
	HandleListWillAnswerRequest():
	int {

		if(!Atlantis\Library::Get(Atlantis\Key::ConfPageEnableDB))
		return Avenue\Response::CodeForbidden;

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/page/edit/:PageID:')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	HandleEdit(int $PageID, Atlantis\Page\Entity $Page):
	void {

		Common\Dump::Var($Page, TRUE);

		return;
	}

	public function
	HandleEditWillAnswerRequest(int $PageID, Avenue\Struct\ExtraData $Data):
	int {

		$Page = Atlantis\Page\Entity::GetByID($PageID);

		if(!$Page)
		return Avenue\Response::CodeNotFound;

		////////

		$Data['Page'] = $Page;

		return Avenue\Response::CodeOK;
	}

}
