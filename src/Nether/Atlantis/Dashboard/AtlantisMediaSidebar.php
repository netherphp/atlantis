<?php

namespace Nether\Atlantis\Dashboard;

class AtlantisMediaSidebar
extends SidebarGroup {

	public int
	$Priority = -4;

	public function
	__Construct() {
		parent::__Construct('Media');

		($this->Items)
		->Push(new SidebarGroupItem('Browse', '/dashboard/media/browse', 'mdi-view-grid'));

		return;
	}

}