<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

class PrototypeWeb
extends Atlantis\ProtectedWeb {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/ops/pti')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	Index():
	void {

		$PIndex = Atlantis\Struct\PrototypeIndex::Find([
			'Page' => 1,
			'Limit' => 25
		]);

		$PIndex->Each(
			fn($P)=> $P->Get()
		);

		Common\Dump::Var($PIndex->Export(), TRUE);

		return;
	}


}
