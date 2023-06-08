<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\Storage;
use Nether\User;

class StorageWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/storage/config')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleConfigGet():
	void {

		$LibInfo = new Storage\Struct\LibraryConfigInfo;

		$this->Surface
		->Set('Page.Title', 'Storage Config Info')
		->Wrap('admin/storage/config', [
			'LibInfo' => $LibInfo
		]);

		return;
	}

}
