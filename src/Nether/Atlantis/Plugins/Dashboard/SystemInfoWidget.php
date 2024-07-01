<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugins\Dashboard;

use Nether\Atlantis;

################################################################################
################################################################################

class SystemInfoWidget
extends Atlantis\Plugin
implements Atlantis\Plugin\Interfaces\Dashboard\InfoWidgetInterface {

	public function
	GetSorting():
	int {

		return 500;
	}

	public function
	GetColumnSizes():
	string {

		return 'col-12';
	}

	public function
	Allow():
	bool {

		return $this->App->User->IsAdmin();
	}

	public function
	Render():
	string {

		$Output = (
			($this->App->Surface)
			->GetArea('atlantis/dashboard/infowidget/system', [

			])
		);

		return $Output;
	}

};
