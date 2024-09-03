<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Util;

use Nether\Common;
use Stringable;
use Exception;

################################################################################
################################################################################

class CacheBuster
implements Stringable {

	public ?string
	$Filename = NULL;

	public ?string
	$Data = NULL;

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Stringable ///////////////////////////////////////

	public function
	__ToString():
	string {

		return $this->Data;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Generate():
	static {

		$this->Data = Common\UUID::V7();

		return $this;
	}

	public function
	Should():
	bool {

		return $this->Data !== NULL;
	}

	public function
	RewriteURL(string $URL):
	string {

		if($this->Data === NULL)
		return $URL;

		return (
			(str_contains($URL, '?'))
			? "{$URL}&v={$this->Data}"
			: "{$URL}?v={$this->Data}"
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $Path):
	static {

		$Output = new static;
		$Err = NULL;

		////////

		try {
			$Output->Data = Common\Filesystem\Util::TryToReadFile($Path);
			$Output->Filename = $Path;
		}

		catch(Exception $Err) {

		}

		////////

		return $Output;
	}

};
