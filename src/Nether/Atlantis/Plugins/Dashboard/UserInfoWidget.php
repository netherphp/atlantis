<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugins\Dashboard;

use Nether\Atlantis;
use Nether\User;

################################################################################
################################################################################

class UserInfoWidget
extends Atlantis\Plugin
implements Atlantis\Plugin\Interfaces\Dashboard\InfoWidgetInterface {

	public function
	GetSorting():
	int {

		return 100;
	}

	public function
	GetColumnSizes():
	string {

		return 'col-12';
	}

	public function
	Allow():
	bool {

		return TRUE;
	}

	public function
	Render():
	string {

		$Output = (
			($this->App->Surface)
			->GetArea('atlantis/dashboard/infowidget/user', [

			])
		);

		return $Output;
	}

};
