<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Email;
use Nether\Storage;

class IndexWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	MainGet():
	void {

		$this->Surface
		->Set('Page.Title', 'Admin')
		->Wrap('admin/index', [
			'EmailConfigInfo'   => new Email\Struct\LibraryConfigInfo,
			'StorageConfigInfo' => new Storage\Struct\LibraryConfigInfo
		]);

		return;
	}

}
