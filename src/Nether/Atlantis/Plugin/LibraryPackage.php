<?php

namespace Nether\Atlantis\Plugin;

use Nether\Atlantis;
use Nether\Common;

trait LibraryPackage {

	public Common\Datastore
	$PluginInterfaces;

	public function
	RegisterPluginInterface(string $IFace):
	static {

		if(!isset($this->PluginInterfaces))
		$this->PluginInterfaces = new Common\Datastore;

		$this->PluginInterfaces[$IFace] = TRUE;

		return $this;
	}

	public function
	HasPluginInterface(string $IFace):
	bool {

		return $this->PluginInterfaces->HasKey($IFace);
	}

	public function
	GetPluginInterface(string $IFace):
	mixed {

		return $this->PluginInterfaces->Get($IFace);
	}

}
