<?php

namespace Nether\Atlantis\Dashboard;

class SidebarGroupItem {

	public string
	$Title;

	public string
	$URL;

	public string
	$Icon;

	public function
	__Construct(string $Title, string $URL, string $Icon='mdi-cog') {

		$this->Title = $Title;
		$this->URL = $URL;
		$this->Icon = $Icon;

		return;
	}

}
