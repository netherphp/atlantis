<?php

namespace Nether\Atlantis;

class Util {

	static public function
	ReadFromGitIgnore(string $Filename='.gitignore'):
	?array {

		if(!file_exists($Filename))
		return NULL;

		if(!is_readable($Filename))
		return NULL;

		$Output = file($Filename);
		return $Output;
	}

	static public function
	WriteToGitIgnore(array $Lines, string $Filename='.gitignore'):
	void {

		$Lines = static::ReadFromGitIgnore($Filename);

		var_dump($Lines);

		return;
	}

}
