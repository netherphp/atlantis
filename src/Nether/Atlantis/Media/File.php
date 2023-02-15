<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Storage;

use Exception;
use Imagick;

#[Database\Meta\TableClass('Uploads', 'UP')]
#[Database\Meta\InsertUpdate]
#[Database\Meta\InsertReuseUnique]
class File
extends Atlantis\Prototype {

	const
	TypeFile = 'file',
	TypeImg  = 'img';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE, Nullable: FALSE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
	#[Database\Meta\FieldIndex(Unique: TRUE)]
	public string
	$UUID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Users', 'ID', Delete: 'SET NULL')]
	public int
	$UserID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$Size;

	#[Database\Meta\TypeVarChar(Size: 128, Nullable: FALSE)]
	public string
	$Name;

	#[Database\Meta\TypeVarChar(Size: 8, Default: 'file', Nullable: FALSE)]
	public string
	$Type;

	#[Database\Meta\TypeVarChar(Size: 255, Nullable: FALSE)]
	public string
	$URL;

	#[Database\Meta\TypeText]
	public ?string
	$ExtraFilesJSON;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public Common\Date
	$DateCreated;

	public Common\Datastore
	$ExtraFiles;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->DateCreated = Common\Date::FromTime($this->TimeCreated);
		$this->ExtraFiles = new Common\Datastore;

		if($this->ExtraFilesJSON) {
			$Data = json_decode($this->ExtraFilesJSON, TRUE);

			if(is_array($Data))
			$this->ExtraFiles->SetData($Data);

			unset($Data);
		}

		return;
	}

	public function
	Drop():
	static {

		$File = $this->GetFile();

		parent::Drop();

		$File->DeleteParentDirectory();

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetFile(?string $Name=NULL):
	Storage\File {

		$URL = $this->URL;

		// find a file sitting next to this one if asked.

		if($Name !== NULL)
		$URL = sprintf('%s/%s', dirname($URL), $Name);

		////////

		$File = Storage\Manager::GetFileByURL($URL);

		return $File;
	}

	public function
	GetSizeReadable():
	string {

		$Bytes = new Common\Units\Bytes($this->Size);

		return $Bytes->Get();
	}

	public function
	GetPreviewURL(?string $Name=NULL):
	?string {

		if(!$this->ExtraFiles->HasKey($Name))
		$Name = NULL;

		$File = $this->GetFile($Name);

		return $File->GetPublicURL();
	}

	public function
	GetPublicURL():
	?string {

		if(str_starts_with($this->URL, 'storage://'))
		return $this->GetFromStorageURL();

		return NULL;
	}

	public function
	GetFromStorageURL():
	?string {

		$Mgr = new Storage\Manager;
		$Found = NULL;

		preg_match(
			'#^storage://([^/]+?)(/.+)$#',
			$this->URL,
			$Found
		);

		if(count($Found) !== 3)
		throw new Exception('storage url seems malformed');

		$Storage = $Mgr->Location($Found[1]);

		if($Storage === NULL)
		throw new Exception("storage {$Found[1]} not defined");

		return $Storage->GetPublicURL(ltrim($Found[2], '/'));
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GenerateExtraFiles():
	static {

		if($this->Type === static::TypeImg)
		$this->GenerateImageThumbnails();

		return $this;
	}

	public function
	GenerateImageThumbnails():
	static {

		$File = $this->GetFile();
		$Ext = 'jpeg';

		$Sizes = [
			'lg.{Ext}'=> [ 1024, 1024 ],
			'sm.{Ext}'=> [ 400, 400 ]
		];

		$Name = NULL;
		$Dim = NULL;
		$New = NULL;

		foreach($Sizes as $Name => $Dim) {
			$Name = str_replace('{Ext}', $Ext, $Name);
			$New = $this->GenerateImageFit($File, $Name, $Dim[0], $Dim[1]);

			$this->ExtraFiles[$Name] = $New->GetSize();
		}

		$this->Update([
			'ExtraFilesJSON' => json_encode($this->ExtraFiles)
		]);

		return $this;
	}

	public function
	GenerateImageFit(Storage\File $File, string $Name, int $W, int $H):
	Storage\File {

		try {
			$Img = new Imagick;
			$Img->ReadImageBlob($File->Read());
			$Img->ResizeImage($W, $H, Imagick::FILTER_CATROM, 1.0, TRUE);
			$Img->SetFormat('jpeg');
			$Img->SetImageCompression(Imagick::COMPRESSION_JPEG);
			$Img->SetCompressionQuality(92);

			$Data = $Img->GetImageBlob();
			$Img->Destroy();
		}

		catch(Exception $Err) {
			throw new Exception("imagick hated that: {$Err->GetMessage()}");
		}

		////////

		try {
			$Output = $File->New($Name);
			$Output->Write($Data);
		}

		catch(Exception $Err) {
			throw new Exception("storage hated that: {$Err->GetMessage()}");
		}

		////////

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Common\Datastore($Input);
		$Input->BlendRight([
			'Type'        => 'file',
			'UUID'        => Common\UUID::V7(),
			'TimeCreated' => time()
		]);

		return parent::Insert($Input);
	}

}
