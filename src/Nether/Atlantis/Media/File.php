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

		if(isset($this->ExtraFilesJSON)) {
			$Data = json_decode($this->ExtraFilesJSON, TRUE);

			if(!is_array($Data))
			$Data = [];

			($this->ExtraFiles)
			->SetData($Data)
			->Remap(fn(mixed $Row)=> new ExtraFile($Row));

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
	GetEntityInfo():
	array {

		return match($this->Type) {
			'img' => [
				'ID'  => $this->ID,
				'URL' => [
					'SM' => $this->GetPreviewURL('sm.'),
					'MD' => $this->GetPreviewURL('md.'),
					'LG' => $this->GetPreviewURL('lg.'),
					'OG' => $this->GetPublicURL()
				]
			],
			default => [
				'ID'  => $this->ID,
				'URL' => $this->GetPublicURL()
			]
		};
	}

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
	GetSizeReadable(bool $IncExtraFiles=FALSE):
	string {

		$Size = $this->Size;

		if($IncExtraFiles)
		$Size += $this->ExtraFiles->Accumulate(0,
			fn(int $C, ExtraFile $Val)
			=> $C + $Val->Size
		);

		$Bytes = new Common\Units\Bytes($Size);

		return $Bytes->Get();
	}

	public function
	GetPreviewURL(?string $Query=NULL, bool $ElseOG=FALSE):
	?string {

		$FName = NULL;
		$FExtra = NULL;

		////////

		if($Query === NULL)
		return $this->GetPublicURL();

		////////

		if(str_ends_with($Query, '.')) {
			foreach($this->ExtraFiles as $FName => $FExtra) {
				/** @var Atlantis\Media\ExtraFile $FExtra */
				if($FExtra->Type === $FExtra::TypeImgSet)
				if(str_starts_with($FName, $Query))
				return $this->GetFile($FName)->GetPublicURL();
			}
		}

		if($ElseOG)
		return $this->GetPublicURL();

		return '';
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
	CleanExtraFiles():
	void {

		$MainFile = basename($this->URL);
		$File = $this->GetFile();
		$List = $File->Storage->List($File->GetParentDirectory());
		$Found = NULL;

		foreach($List as $Found) {
			$CurFile = basename($Found);

			if($CurFile === $MainFile)
			continue;

			$File->Storage->Delete($Found);
		}

		$this->ExtraFiles->Clear();
		$this->Update([ 'ExtraFilesJSON'=> NULL ]);

		return;
	}

	public function
	GenerateExtraFiles():
	static {

		$File = $this->GetFile();
		$this->ExtraFiles->Clear();

		try {
			if($this->Type === static::TypeImg)
			$this->GenerateImageThumbnails();
		}

		catch(Exception $E) {
			$this->CleanExtraFiles();
		}

		return $this;
	}

	public function
	GenerateImageThumbnails():
	static {

		$File = $this->GetFile();
		$Ext = match($File->GetExtension()) {
			'png'   => 'png',
			'gif'   => 'gif',
			default => 'jpeg'
		};

		$Sizes = match($Ext) {
			'gif' => [
				'sm.{Ext}'=> [ 250, 250 ]
			],
			default => [
				'lg.{Ext}'=> [ 1920, 1920 ],
				'md.{Ext}'=> [ 800, 800 ],
				'sm.{Ext}'=> [ 250, 250 ]
			]
		};

		$Name = NULL;
		$Dim = NULL;
		$New = NULL;

		foreach($Sizes as $Name => $Dim) {
			$Name = str_replace('{Ext}', $Ext, $Name);
			$New = $this->GenerateImageFit($File, $Name, $Dim[0], $Dim[1], $Ext);

			$this->ExtraFiles[$Name] = new ExtraFile([
				'Type' => ExtraFile::TypeImgSet,
				'Size' => $New->GetSize()
			]);
		}

		$this->Update([
			'ExtraFilesJSON' => json_encode($this->ExtraFiles)
		]);

		return $this;
	}

	public function
	GenerateImageFit(Storage\File $File, string $Name, int $W, int $H, string $Ext):
	Storage\File {

		$Swapper = function(Imagick $Old, Imagick $New){
			$Old->Clear();
			return $New;
		};

		$Data = NULL;

		try {
			if(!($Data = $File->Read()))
			throw new Exception('failed to read data');

			$Img = new Imagick;
			$Img->ReadImageBlob($File->Read());
			$Img->ResetIterator();
			$Img->StripImage();
			$IP = $Img->GetImagePage();
			$IW = $IP['width'];
			$IH = $IP['height'];

			$Per = min(($W / $IW), ($H / $IH));
			$Per = round($Per, 8);

			$FW = (int)($IW * $Per);
			$FH = (int)($IH * $Per);

			$MyWay = TRUE;

			switch($Ext) {
				case 'png':
					$Img->SetFormat('png');

					if($IW > $W || $IH > $H)
					$Img->ResizeImage($W, $H, Imagick::FILTER_LANCZOS, 1.0, TRUE);
				break;
				case 'gif':
					$Img->SetFormat('gif');
					$Img->ResetIterator();

					// everything i tried to resize the image layers
					// manually-but-easy would result in what
					// seemed like rounding errors, sometimes things
					// would be a fraction off leaving artificts from
					// previous frames.

					// new theory is to recompose the image in a way
					// that nukes all the stupid offsets and stuff
					// allowing all the scaling to use the same
					// numbers hopefully making all the layers look
					// good on top of eachother after they dispose.

					// all of this is because the coaleseImages way
					// everyone says to do and does work good, also
					// destroys the gif optimisations making a gif
					// half the size weight twice as much, and the
					// imagick optimise method cant make up for it.

					if($MyWay)
					if($IW > $W || $IH > $H) {
						$New = new Imagick;
						$New->SetFormat('gif');
						$Iter = 0;

						while($Img->NextImage()) {
							$Iter += 1;
							$FP = $Img->GetImagePage();

							$New->NewImage($FP['width'], $FP['height'], 'transparent');
							$New->SetImageDispose($Img->GetImageDispose());
							$New->SetImageDelay($Img->GetImageDelay());
							$New->CompositeImage($Img, Imagick::COMPOSITE_COPY, $FP['x'], $FP['y']);
							$New->ResizeImage($FW, $FH, Imagick::FILTER_BOX, 0.0);

							// release old frames.
							$Img->RemoveImage();

							// reset iter as the above ruined it.
							// chug frame 0 until no frames.
							$Img->ResetIterator();
						}

						$Img->Clear();
						$Img = $New;
					}

					if(!$MyWay)
					if($IW > $W || $IH > $H) {
						$Img = $Swapper($Img, $Img->CoalesceImages());
						$Img->ResetIterator();

						while($Img->NextImage()) {
							$Img->ResizeImage($FW, $FH, Imagick::FILTER_BOX, 0.0);
						}

						$Img = $Swapper($Img, $Img->OptimizeImageLayers());

						/** @var Imagick $Img */
						// apparently the above method is documented
						// wrong. as of 2023-02-17 it is returning
						// an imagick object not a bool. and the
						// imagemagick source code claims that
						// OptimizeLayerFrames does return an obj.

						$Img = $Swapper($Img, $Img->DeconstructImages());
					}

				break;
				default:
					$Img->SetFormat('jpeg');
					$Img->SetImageCompression(Imagick::COMPRESSION_JPEG);
					$Img->SetCompressionQuality(90);

					if($IW > $W || $IH > $H)
					$Img->ResizeImage($W, $H, Imagick::FILTER_LANCZOS, 1.0, TRUE);
				break;
			}

			//echo 'get image data', PHP_EOL;
			$Data = $Img->GetImagesBlob();

			//echo 'free image resources', PHP_EOL;
			$Img->Clear();
			unset($Img);
		}

		catch(Exception $Err) {
			//throw new Exception("imagick hated that: {$Err->GetMessage()}");
			throw $Err;
		}

		////////

		try {
			//echo "write to disk", PHP_EOL;
			$Output = $File->New($Name);
			$Output->Write($Data);
			echo '> Wrote ', new Common\Units\Bytes($Output->GetSize()), PHP_EOL;
		}

		catch(Exception $Err) {
			throw new Exception("storage hated that: {$Err->GetMessage()}");
		}

		echo '> Memory: ', new Common\Units\Bytes(memory_get_peak_usage()), PHP_EOL;

		////////

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Sort'] ??= 'newest';

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
		}

		return;
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
