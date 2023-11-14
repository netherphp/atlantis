<?php

namespace Nether\Atlantis\Plugins\Dashboard;

use Nether\Atlantis;

interface OverviewElementButtonInterface {

	public function
	__Construct(Atlantis\Engine $App);

	public function
	GetShow():
	bool;

	public function
	GetName():
	?string;

	public function
	GetIcon():
	?string;

	public function
	GetURL():
	?string;

};
