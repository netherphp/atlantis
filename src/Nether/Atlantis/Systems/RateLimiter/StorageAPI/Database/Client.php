<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\RateLimiter\StorageAPI\Database;

use Nether\Atlantis;
use Nether\Common;
use Nether\Avenue;

use Nether\Atlantis\Systems\RateLimiter\StorageAPI\ClientInterface;
use Nether\Atlantis\Systems\RateLimiter\StorageAPI\EntryInterface;

################################################################################
################################################################################

class Client
implements ClientInterface {

	protected Atlantis\Engine
	$App;

	public function
	__Construct(Atlantis\Engine $App) {

		$this->App = $App;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Bump(EntryInterface $Entry, int $Inc):
	static {

		$Entry->Bump($Inc);

		return $this;
	}

	public function
	Delete(EntryInterface $Entry):
	static {

		$Entry->Delete();

		return $this;
	}

	public function
	Fetch(string $HitHash):
	?Row {

		$Row = Row::GetByField('HitHash', $HitHash);

		return $Row;
	}

	public function
	Touch(string $HitHash, int $MaxAttempts):
	Row {

		// unique index bashing.

		$Row = Row::Insert([
			'HitHash'    => $HitHash,
			'TimeLogged' => Common\Date::Unixtime(),
			'Remaining'  => $MaxAttempts
		]);

		return $Row;
	}

};
