<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugin\Interfaces\Dashboard;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

interface InfoWidgetInterface {

	public function
	GetSorting():
	int;

	#[Common\Meta\Info('Return a string of Bootstrap column sizes.')]
	public function
	GetColumnSizes():
	string;

	#[Common\Meta\Info('Return true if the widget should be shown to the user.')]
	public function
	Allow():
	bool;

	#[Common\Meta\Info('Return the rendered HTML of the widget.')]
	public function
	Render():
	string;

};
