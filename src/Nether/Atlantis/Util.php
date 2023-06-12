<?php

namespace Nether\Atlantis;

use ReCaptcha;
use Nether\Atlantis;
use Nether\Common;

use FilesystemIterator;
use Generator;
use Exception;

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
		return 'checked="checked"';

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
		$File = NULL;

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

	static public function
	AppendGoto(string $URL, string $What):
	string {

		$Encoded = Common\Datafilters::Base64Encode($What);
		$Output = $URL;

		if(str_contains($URL, '?'))
		$Output .= "&goto={$Encoded}";
		else
		$Output .= "?goto={$Encoded}";

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	IfOneOrMore(int $Count, mixed $Singular, mixed $Plural):
	mixed {

		return match($Count) {
			1       => $Singular,
			default => $Plural
		};
	}

	static public function
	IsReCaptchaValid(Atlantis\Engine $App):
	bool {

		$ApiEnabled = $App->Config['Google.ReCaptcha.Enabled'];
		$ApiKey = $App->Config['Google.ReCaptcha.PrivateKey'];

		if(!$ApiEnabled)
		return TRUE;

		if($ApiEnabled && !$ApiKey)
		throw new Exception('Missing Google.ReCaptcha.PrivateKey in config.');

		////////

		$Response = (
			$App->Router->Request->Data
			->Get('g-recaptcha-response')
		);

		$RemoteAddr = (
			isset($_SERVER['REMOTE_ADDR'])
			? $_SERVER['REMOTE_ADDR']
			: NULL
		);

		////////

		$ReCaptcha = new ReCaptcha\ReCaptcha($ApiKey);
		$ReResult = $ReCaptcha->Verify($Response, $RemoteAddr);

		return (Bool)$ReResult->IsSuccess();
	}

}
