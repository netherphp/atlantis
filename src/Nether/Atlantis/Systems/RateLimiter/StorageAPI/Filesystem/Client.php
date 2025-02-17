<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\RateLimiter\StorageAPI\Filesystem;

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
	?File {

		$Filename = $this->GetFilename($HitHash);
		$File = File::Read($Filename);

		return $File;
	}

	public function
	Touch(string $HitHash, int $MaxAttempts):
	File {

		$Filename = $this->GetFilename($HitHash);

		$File = File::Touch(
			$Filename,
			$HitHash,
			Common\Date::Unixtime(),
			$MaxAttempts
		);

		return $File;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetFilename(string $HitHash):
	string {

		$Output = $this->App->FromProjectRoot(sprintf(
			'temp/atl-rate-limiter/%s.json',
			hash('md5', $HitHash)
		));

		return $Output;
	}

};
