<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class MediaDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/media/browse')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	public function
	Browse():
	void {

		$Items = Atlantis\Media\File::Find([
			'Page'  => 1,
			'Limit' => 24
		]);

		($this->Surface)
		->Wrap('media/dashboard/browse', [
			'Items' => $Items
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/dashboard/media/view/:FileID:')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(int $FileID, Atlantis\Media\File $File):
	void {

		($this->Surface)
		->Wrap('media/dashboard/view', [
			'Item' => $File,
			'Tags' => $File->GetTagLinks()
		]);

		return;
	}

	protected function
	ViewWillAnswerRequest(int $FileID, Common\Datastore $ExtraData):
	int {

		$File = Atlantis\Media\File::GetByID($FileID);

		if(!$File)
		return Avenue\Response::CodeNotFound;

		$ExtraData['File'] = $File;

		return Avenue\Response::CodeOK;
	}

}
