<?php

namespace Nether\Atlantis;

use FilesystemIterator;
use Generator;

class Util {

	static public function
	GetSelectedHTML(bool $Cond):
	string {

		if($Cond)
		return 'selected="selected"';

		return '';
	}

	static public function
	GetCheckedHTML(bool $Cond):
	string {

		if($Cond)
		return 'selected="selected"';

		return '';
	}

	static public function
	PrintHTML(?string $Input):
	void {

		if($Input === NULL)
		$Input = '';

		echo Filter::EncodeHTML($Input);
		return;
	}

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

		// try to avoid breaking on windows with this one easy trick

		$Source = static::Repath($Source);
		$Dest = static::Repath($Dest);

		// if we're talking about a file then just copy it and be done.

		if(is_file($Source)) {
			copy($Source, $Dest);
			return file_exists($Dest);
		}

		// if we are talking about a directory then recursively dive it.

		Util::MkDir($Dest);

		$Iter = new FilesystemIterator($Source, (
			FilesystemIterator::CURRENT_AS_FILEINFO |
			FilesystemIterator::SKIP_DOTS
		));

		foreach($Iter as $File) {
			$Suffix = str_replace(
				"{$Source}/", '',
				str_replace('\\', '/', $File->GetPathname())
			);

			Util::Copy($File, "{$Dest}/{$Suffix}");
		}

		return file_exists($Dest);
	}

	static public function
	CopyWithConfirm(string $Source, string $Dest, bool $Force=FALSE):
	Generator {

		$Source = static::Repath($Source);
		$Dest = static::Repath($Dest);
		$Keep = NULL;

		if(!file_exists($Source))
		return FALSE;

		if(is_file($Source)) {
			if($Force)
			$Keep = FALSE;
			else
			$Keep = file_exists($Dest) ? yield $Dest : FALSE;

			if(!$Keep)
			copy($Source, $Dest);

			return file_exists($Dest);
		}

		////////

		if(!static::MkDir($Dest))
		return FALSE;

		$Iter = new FilesystemIterator($Source, (0
			| FilesystemIterator::CURRENT_AS_FILEINFO
			| FilesystemIterator::SKIP_DOTS
		));

		$FileInfo = NULL;
		$Path = NULL;
		$Final = NULL;
		$Result = NULL;

		foreach($Iter as $FileInfo) {
			$Path = static::Repath($FileInfo->GetPathname());
			$Final = str_replace("{$Source}/", "{$Dest}/", $Path);

			foreach(static::CopyWithConfirm($Path, $Final) as $Result)
			if(!$Force)
			yield $Result;
		}

		return file_exists($Dest);
	}

	static public function
	MkDir(string $Path):
	bool {

		$Path = str_replace('\\', '/', $Path);

		if(!file_exists($Path)) {
			$UMask = umask(0);
			mkdir($Path, 0777, TRUE);
			umask($UMask);
		}

		return is_dir($Path);
	}

	static public function
	Repath(string $Input):
	string {

		return str_replace('\\', '/', $Input);
	}

}
