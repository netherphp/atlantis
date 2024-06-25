<?php

namespace Nether\Atlantis\Routes;

use FileEye;
use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Ki;
use Nether\Storage;

use Exception;
use SplFileInfo;

class UploadAPI
extends Atlantis\ProtectedAPI {

	use
	Ki\CallbackPackage;

	const
	MatchContentRange  = '/(bytes) ([\d]+)-([\d]+)\/([\d]+)/',
	HeaderContentRange = 'HTTP_CONTENT_RANGE';

	const
	KiOnUploadComplete = 'Atlantis.FileUploader.OnUploadComplete',
	KiOnUploadFinalise = 'Atlantis.FileUploader.OnUplaodFinalise';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	## [Avenue\Meta\RouteHandler('/api/upload/chunk', Verb: 'POST')] #########
	public function
	ChunkPost():
	void {

		$Storage = $this->App->Storage->Location('Temp');

		if(!($Storage instanceof Storage\Adaptors\Local))
		$this->Quit(4, 'no Temp storage or not is not a Local adaptor.');

		////////

		if(!isset($_FILES['file']))
		$this->Quit(1, 'no files selected');

		if(!isset($_SERVER[static::HeaderContentRange]))
		$this->Quit(2, 'not a chunked upload');

		if($_FILES['file']['size'] === 0)
		$this->Quit(3, 'upload error (usually means exceeded upload max filesize)');

		////////

		$Type = Common\Filters\Text::Trimmed(
			$this->Data->Type
			?: 'default'
		);

		$UUID = Common\Filters\Text::Trimmed(
			$this->Data->UUID
			?: Common\UUID::V7()
		);

		$TempPath = 'upl';
		$TempName = "tmp-{$this->User->ID}-{$UUID}";
		$TempResting = "{$TempPath}/{$TempName}";

		////////

		$Range = static::DigestChunkRange(
			$_SERVER[static::HeaderContentRange]
		);

		$File = $_FILES['file'];
		$Info = new SplFileInfo($File['tmp_name']);
		$Size = $Info->GetSize();

		////////

		// if we are starting a new file.

		if($Range->Begin === 0) {
			$Storage->Put(
				$TempResting,
				file_get_contents($File['tmp_name'])
			);
		}

		// if we are continuing a chunked file.

		else {
			if(!$this->Data->UUID)
			$this->Quit(4, 'how are you on a middle chunk without having a uuid?');

			$Storage->Append(
				$TempResting,
				file_get_contents($File['tmp_name'])
			);
		}

		// if we have finished a chunked file.

		if($Range->End === $Range->Total) {
			$FinalPath = sprintf('upl/%s', $UUID);
			$FinalResting = sprintf(
				'%s/original.%s',
				$FinalPath,
				static::DetermineCommonExt($File['name'])
			);

			////////

			$Storage->Rename($TempResting, $FinalResting);
			$Storage->Chmod($FinalResting, 0666);

			$Source = $Storage->GetFileObject($FinalResting);
		}

		////////

		unlink($File['tmp_name']);

		$this
		->SetPayload([
			'UUID'        => $UUID,
			'Name'        => $File['name'],
			'Type'        => $Type,
			'ChunkSize'   => $Size,
			'Range'       => $Range
		]);

		return;
	}

	## [Avenue\Meta\RouteHandler('/api/upload/chunk', Verb: 'POSTFINAL')] ####
	public function
	ChunkFinalise():
	void {

		($this->Data)
		->Type([
			Common\Filters\Text::Trimmed(...),
			fn(Common\Struct\DatafilterItem $V)=> $V->Value ?: 'default'
		])
		->UUID(Common\Filters\Text::UUID(...));

		////////

		$Storage = $this->App->Storage->Location('Temp');

		$File = $Storage->GetFileObject(sprintf(
			'upl/%s/original.%s',
			$this->Data->UUID,
			static::DetermineCommonExt($this->Data->Name)
		));

		if(!$File->Exists())
		throw new Atlantis\Error\Media\InvalidUpload($this->Data->UUID);

		// see if there is a reason to bail.

		$this->OnFinaliseInspectFile($File);

		////////

		$Libs = $this->App->Library->Distill(
			fn(Common\Library $Lib)
			=> $Lib instanceof Atlantis\Plugins\UploadHandlerInterface
		);

		$Libs->Each(
			fn(Atlantis\Plugins\UploadHandlerInterface $Lib)
			=> (
				$File->Exists()
				? $Lib->OnUploadFinalise(
					$this->App,
					$this->Data->UUID,
					$this->Data->Name,
					$this->Data->Type,
					$File
				)
				: FALSE
			)
		);

		return;
	}

	protected function
	OnFinaliseInspectFile(Storage\File $File):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	PrepareStorageLocation(string $StorageName='Default'):
	Storage\Adaptor {

		$Storage = $this->App->Storage->Location($StorageName);

		if(!$Storage)
		throw new Exception("no storage location {$StorageName} defined");

		return $Storage;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DetermineCommonExt(string $Name):
	string {

		$Ext = NULL;
		$Type = NULL;

		////////

		try {
			$Ext = new FileEye\MimeMap\Extension(
				str_contains($Name, '.')
				? substr($Name, (strrpos($Name, '.') + 1))
				: 'txt'
			);

			$Type = new FileEye\MimeMap\Type(
				$Ext->GetDefaultType()
			);

			return strtolower($Type->GetDefaultExtension());
		}

		catch(Exception $Error) { }

		////////

		return 'txt';
	}

	static public function
	DigestChunkRange(string $Content):
	?object {

		$Match = NULL;
		$Result = preg_match(static::MatchContentRange, $Content, $Match);

		if(!$Result)
		return NULL;

		return (object)[
			'Unit'  => $Match[1],
			'Begin' => (int)$Match[2],
			'End'   => (int)$Match[3],
			'Total' => (int)$Match[4]
		];
	}

}