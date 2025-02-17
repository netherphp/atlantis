<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\RateLimiter\StorageAPI\Database;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

#[Database\Meta\TableClass('AtlRateLimiterData', 'AtlRLD', Engine: 'MEMORY')]
#[Database\Meta\InsertReuseUnique]
#[Database\Meta\InsertUpdate]
class Row
extends Atlantis\Prototype
implements Atlantis\Systems\RateLimiter\StorageAPI\EntryInterface {

	#[Database\Meta\TypeChar(Size: 128)]
	#[Database\Meta\FieldIndex(Unique: TRUE)]
	protected string
	$HitHash;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	protected int
	$TimeLogged;

	#[Database\Meta\TypeIntSmall(Unsigned: TRUE)]
	protected int
	$Remaining;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Bump(int $Inc):
	int {

		$this->Update([
			'Remaining' => ($this->Remaining + $Inc)
		]);

		return $this->Remaining;
	}

	public function
	Delete():
	void {

		$this->Drop();

		return;
	}

	public function
	GetHitHash():
	string {

		return $this->HitHash;
	}

	public function
	GetTimeLogged():
	int {

		return $this->TimeLogged;
	}

	public function
	GetAttemptsRemaining():
	int {

		return $this->Remaining;
	}

};
