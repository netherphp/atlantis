<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\RateLimiter\StorageAPI\Filesystem;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

class File
extends Common\Prototype
implements
	Atlantis\Systems\RateLimiter\StorageAPI\EntryInterface,
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON {

	////////////////////////////////////////////////////////////////

	protected string
	$Filename;

	protected string
	$HitHash;

	protected int
	$TimeLogged;

	protected int
	$Remaining;

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS EntryInterface ///////////////////////////////////

	public function
	Bump(int $Inc):
	int {

		$this->Remaining += $Inc;
		$this->Write();

		return $this->Remaining;
	}

	public function
	Delete():
	void {

		unlink($this->Filename);

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ToArray():
	array	{

		$Output = [
			'HitHash'    => $this->HitHash,
			'TimeLogged' => $this->TimeLogged,
			'Remaining'  => $this->Remaining
		];

		return $Output;
	}

	public function
	ToJSON():
	string {

		$JSON = Common\Filters\Text::ReadableJSON($this->ToArray());

		return $JSON;
	}

	public function
	SetFilename(string $Filename):
	static {

		$this->Filename = $Filename;

		return $this;
	}

	public function
	Write():
	void {

		file_put_contents($this->Filename, $this->ToJSON());

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Read(string $Filename):
	?static {

		$Data = NULL;
		$Output = NULL;

		/////////

		try {
			$Data = Common\Filesystem\Util::TryToReadFileJSON($Filename);
		}

		catch(Common\Error\FileNotFound $Err) {
			return NULL;
		}

		$Output = new static($Data);
		$Output->SetFilename($Filename);

		return $Output;
	}

	static public function
	Touch(string $Filename, string $HitHash, int $TimeLogged, int $MaxAttempts):
	static {

		$Dirname = dirname($Filename);

		////////

		if(!file_exists($Dirname))
		Common\Filesystem\Util::MkDir($Dirname);

		$Output = new static([
			'Filename'    => $Filename,
			'HitHash'     => $HitHash,
			'TimeLogged'  => $TimeLogged,
			'Remaining'   => $MaxAttempts
		]);

		$Output->Write();

		////////

		return $Output;
	}

};
