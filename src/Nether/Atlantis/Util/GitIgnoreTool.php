<?php

namespace Nether\Atlantis\Util;

use Nether\Common;

use Exception;

class GitIgnoreTool {
/*//
a small utility for doing the most basic management of .gitignore files so
things can be programatically added to it.
//*/

	public string
	$Filename;

	public array
	$Lines;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Filename) {

		$this->Filename = $Filename;
		return;
	}

	public function
	Read():
	static {

		if(!file_exists($this->Filename))
		throw new Exception("{$this->Filename} not found");

		if(!is_readable($this->Filename))
		throw new Exception("{$this->Filename} not readable");

		////////

		$this->Lines = array_map(
			(fn($Line)=> trim($Line)),
			(file($this->Filename) ?: [])
		);

		return $this;

	}

	public function
	Write():
	static {

		if(file_exists($this->Filename)) {
			if(!is_writable($this->Filename))
			throw new Common\Error\FileUnwritable($this->Filename);
		}

		// do not know if it is still true but a long time ago i suffered
		// an issue where the last line of a git ignore file would not be
		// honoured if there was no new line at the end of it and i am
		// still butthurt over it.

		$Output = trim(join(PHP_EOL, $this->Lines));
		$Output .= PHP_EOL;

		file_put_contents($this->Filename, $Output);

		return $this;
	}

	public function
	Empty():
	static {

		unset($this->Lines);
		$this->Lines = [];

		return $this;
	}

	public function
	Append(array $Lines):
	static {

		if(!isset($this->Lines))
		$this->Read();

		////////

		$this->Lines = array_merge(
			array_values($this->Lines),
			[ '' ],
			array_values(array_diff($Lines, $this->Lines))
		);

		return $this;
	}


}
