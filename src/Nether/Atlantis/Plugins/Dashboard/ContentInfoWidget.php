<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugins\Dashboard;

use Nether\Atlantis;

################################################################################
################################################################################

class ContentInfoWidget
extends Atlantis\Plugin
implements Atlantis\Plugin\Interfaces\Dashboard\InfoWidgetInterface {

	public int
	$Tags = 0;

	public int
	$Profiles = 0;

	public int
	$Photos = 0;

	public int
	$Videos = 0;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetSorting():
	int {

		return 0;
	}

	public function
	GetColumnSizes():
	string {

		return 'col-12';
	}

	public function
	Allow():
	bool {

		$CanContentLog = $this->App->User->HasAccessTypeOrAdmin(
			Atlantis\Key::AccessContentLog
		);

		return $CanContentLog;
	}

	public function
	Render():
	string {

		$Area = 'atlantis/dashboard/infowidget/content';

		$this->FetchData();

		$Output = $this->App->Surface->GetArea(
			$Area, [ 'Element'=> $this ]
		);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchData():
	void {

		$this->Tags = Atlantis\Tag\Entity::FindCount([ 'Type'=> 'tag' ]);
		$this->Profiles = Atlantis\Profile\Entity::FindCount([]);
		$this->Photos = Atlantis\Media\File::FindCount([ 'Type'=> 'img' ]);
		$this->Videos = Atlantis\Media\VideoThirdParty::FindCount([]);

		return;
	}

};
