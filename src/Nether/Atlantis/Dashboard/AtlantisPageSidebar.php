<?php

namespace Nether\Atlantis\Dashboard;

class AtlantisPageSidebar
extends SidebarGroup {

	public int
	$Priority = -10;

	public function
	__Construct() {
		parent::__Construct('Site Pages');

		($this->Items)
		->Push(new SidebarGroupItem(
			'View Pages',
			'/dashboard/page/list',
			'mdi-file-document-multiple-outline'
		));

		return;
	}

}