<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Object\Datastore;

class SidebarGroup {

	public string
	$Title;

	public Datastore
	$Items;

	public int
	$Priority = 0;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Title, ?Datastore $Items=NULL) {

		$this->Title = $Title;

		if($Items !== NULL)
		$this->Items = $Items;
		else
		$this->Items = new Datastore;

		return;
	}

}
