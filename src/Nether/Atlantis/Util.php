<?php

namespace Nether\Atlantis;

use FilesystemIterator;

class Util {

	static public function
	ReadFromGitIgnore(string $Filename='.gitignore'):
	?array {

		if(!file_exists($Filename))
		return NULL;

		if(!is_readable($Filename))
		return NULL;

		$Output = array_map(
			(fn($Line)=> trim($Line)),
			(file($Filename) ?: [])
		);

		return $Output;
	}

	static public function
	WriteToGitIgnore(array $Lines, string $Filename='.gitignore'):
	void {

		// find what is already there.
		$Old = static::ReadFromGitIgnore($Filename);

		// find what is missing.
		$New = array_diff($Lines, $Old ?? []);

		// push the new things into the file.
		$File = fopen($Filename, 'a');
		$EOL = PHP_EOL;
		$Line = NULL;

		foreach($New as $Line)
		fwrite($File, "{$Line}{$EOL}");

		fclose($File);

		return;
	}

	static public function
	Copy(string $Source, string $Dest):
	bool {

		// try to avoid breaking on windows.

		$Source = str_replace('\\', '/', $Source);
		$Dest = str_replace('\\', '/', $Dest);

		// if we're talking about a file then just copy it and be done.

		if(is_file($Source)) {
			copy($Source, $Dest);
		}

		// if we are talking about a directory then recursively dive it.

		else {
			Util::MkDir($Dest);

			$Iter = new FilesystemIterator($Source, (
				FilesystemIterator::CURRENT_AS_FILEINFO |
				FilesystemIterator::SKIP_DOTS
			));

			foreach($Iter as $File) {
				$Suffix = str_replace(
					"{$Source}/", '',
					$File->GetPathname()
				);

				Util::Copy($File, "{$Dest}/{$Suffix}");
			}
		}

		return file_exists($Dest);
	}

	static public function
	MkDir(string $Path):
	bool {

		if(!file_exists($Path)) {
			$UMask = umask(0);
			mkdir($Path, 0777, TRUE);
			umask($UMask);
		}

		return is_dir($Path);
	}

}
