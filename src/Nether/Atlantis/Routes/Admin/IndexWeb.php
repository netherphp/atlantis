<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class IndexWeb
extends Atlantis\ProtectedWeb {

	protected function
	FetchUserPrivList():
	Common\Datastore {
		return new Common\Datastore([
			'CMS'    => $this->User->HasAccessTypeOrAdmin(
				Atlantis\Key::PrivManageCMS, 1, 1
			),
			'System' => $this->User->HasAccessTypeOrAdmin(
				Atlantis\Key::PrivManageCMS, 1, 9000
			)
		]);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/ops')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	Index():
	void {

		$UserPrivList = $this->FetchUserPrivList();

		($this)
		->SetPageTitle('Operations')
		->Area('admin/index', [
			'UserPrivList' => $UserPrivList
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/ops/api')]
	#[Atlantis\Meta\RouteAccessTypeAdmin(9000)]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	ApiGet():
	void {

		$Index = (
			($this->App->Router->GetHandlers())
			->Map(fn($V)=> $V->Map(fn($W)=> $W->Path))
		);

		$Verbs = (
			(new Common\Datastore($Index->Keys()))
			->Sort()
		);

		$Endpoints = (
			$Index
			->Accumulate(
				new Common\Datastore([]),
				fn($I, $V)=> $I->MergeRight($V)
			)
			->Unique()
			->Sort()
		);

		($this)
		->SetPageTitle('API Tool // Operations')
		->Area('admin/apitool/index', [
			'Index'     => $Index,
			'Verbs'     => $Verbs,
			'Endpoints' => $Endpoints
		]);

		return;
	}

}
