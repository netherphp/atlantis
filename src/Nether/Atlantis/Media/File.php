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
extends Atlantis\Prototype
implements Atlantis\Interfaces\ExtraDataInterface {

	const
	TypeFile = 'file',
	TypeImg  = 'img';

	const
	EntType = 'Media.Image'; // TODO make File.Entity and update all records.

	use
	Atlantis\Packages\ExtraData;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Users', 'ID', Delete: 'SET NULL')]
	public int
	$UserID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Nullable: TRUE)]
	#[Database\Meta\ForeignKey('Profiles', 'ID', Delete: 'SET NULL')]
	public ?int
	$ProfileID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$Size;

	#[Database\Meta\TypeVarChar(Size: 128, Nullable: FALSE)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
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

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	public Common\Datastore
	$ExtraFiles;

	#[Database\Meta\TableJoin('ProfileID')]
	public Atlantis\Profile\Entity
	$Profile;

	public function
	HasProfile():
	bool {

		if($this->ProfileID === NULL)
		return FALSE;

		if($this->ProfileID === 0)
		return FALSE;

		return TRUE;
	}

	public function
	BootProfile():
	static {

		if($this->BootProfileExisting())
		return $this;

		if($this->BootProfileExistingUnbound())
		return $this;

		////////

		$this->BootProfileFresh();

		return $this;
	}

	protected function
	BootProfileExisting():
	bool {

		// there is already a profile bound and loaded.

		if(isset($this->Profile) && $this->Profile->ID !== 0)
		return TRUE;

		// there is a profile bound but not loaded.

		if(isset($this->ProfileID)) {
			$this->Profile = Atlantis\Profile\Entity::GetByID(
				$this->ProfileID
			);

			return TRUE;
		}

		////////

		return FALSE;
	}

	protected function
	BootProfileExistingUnbound():
	bool {

		$Found = Atlantis\Profile\Entity::GetByField(
			'ParentUUID', $this->UUID
		);

		////////

		if(isset($Found)) {
			$this->Profile = $Found;
			$this->Update([ 'ProfileID'=> $this->Profile->ID ]);
			return TRUE;
		}

		////////

		return FALSE;
	}

	protected function
	BootProfileFresh():
	bool {

		$this->Profile = Atlantis\Profile\Entity::Insert([
			'ParentUUID' => $this->UUID,
			'Title'      => $this->Name,
			'Alias'      => sprintf('file-profile-%d', $this->ID),
			'Details'    => '',
			'Enabled'    => 1
		]);

		$this->Update([ 'ProfileID'=> $this->Profile->ID ]);

		return TRUE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		//$this->DateCreated = Common\Date::FromTime($this->TimeCreated);
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

		//Common\Dump::Var($Args->Input, TRUE);

		if($Args->InputExists('PRO_ID'))
		$this->Profile = Atlantis\Profile\Entity::FromPrefixedDataset($Args->Input);

		return;
	}

	public function
	Drop():
	static {

		// delete the folder that contains all the image sizes.
		// example: data/upl/<part1-time-based-uuid>/<rest-of-uuid>

		$File = $this->GetFile();
		$File->DeleteParentDirectory();

		// delete the folder that contained this folder, but only if that
		// folder is now empty. the first level of folder will get shared
		// by things uploaded in rapid succession to save inodes or wtfe.
		// example: data/upl/<part1-time-based-uuid>/<rest-of-uuid>

		$ParentDir = $File->Storage->GetDirPath(
			$File->GetParentDirectory()
		);

		if($File->Storage->Count($ParentDir) === 0)
		$File->Storage->Delete($ParentDir);

		parent::Drop();
		return $this;
	}

	public function
	DescribeForPublicAPI():
	array {

		return [
			'ID'          => $this->ID,
			'UUID'        => $this->UUID,
			'Name'        => $this->Name,
			'Type'        => $this->Type,
			'DateCreated' => $this->DateCreated->Get(),
			'URL'         => $this->GetPublicURL(),
			'ExtraFiles'  => $this->ExtraFiles->Keys(),
			'ExtraData'   => $this->ExtraData->Export()
		];
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
	GetPublicURL(?string $Size=NULL):
	?string {

		$URL = NULL;

		////////

		if(str_starts_with($this->URL, 'storage://'))
		$URL = $this->GetFromStorageURL();

		if($URL && $Size)
		$URL = str_replace('original.', "{$Size}.", $URL);

		////////

		return $URL;
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
			throw $E;
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
		$Tmp = NULL;
		$GD = NULL;

		try {
			if(!($Data = $File->Read()))
			throw new Exception('failed to read data');

			$Img = new Imagick;

			// handle supporting file formats that our imagick build does
			// not seem to have at the moment.

			if($File->GetExtension() === 'avif') {

				// gd reports avif support is enabled. but then dies
				// when using it saying it is not supported. it looks
				// like libavif is needed and there is not one in the
				// ubuntu focal. need to upgrade the os.

				throw new Exception('AVIF not supported yet');

				// save the avif locally...
				$Tmp = Common\Filesystem\Util::MkTempFile();
				file_put_contents($Tmp, $File->Read());

				// so gd can open it and resave it...
				$GD = imagecreatefromavif($Tmp);
				imagejpeg($GD, $Tmp);
				imagedestroy($GD);

				// so imagick can read it
				$Img->ReadImageBlob(file_get_contents($Tmp));
				unlink($Tmp);
				unset($Tmp, $GD);
			}

			else {
				$Img->ReadImageBlob($File->Read());
			}

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
			//echo '> Wrote ', new Common\Units\Bytes($Output->GetSize()), PHP_EOL;
		}

		catch(Exception $Err) {
			throw new Exception("storage hated that: {$Err->GetMessage()}");
		}

		//echo '> Memory: ', new Common\Units\Bytes(memory_get_peak_usage()), PHP_EOL;

		////////

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		parent::FindExtendOptions($Input);

		$Input->Define([
			'TagsAll' => NULL,
			'Sort'    => 'newest'
		]);

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		if($Input['TagsAll'] !== NULL)
		static::FindExtendFilters_ByEntityFields_TagsAll($SQL, $Input);

		return;
	}

	static protected function
	FindExtendFilters_ByEntityFields_TagsAll(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(!is_iterable($Input['TagsAll']))
		return;

		$TLink = FileTagLink::GetTableInfo();

		$GenTrainAnd = (function() use($SQL, $Input, $TLink) {

			// this method generates a logical and restriction upon the
			// main table by joining each tag over and over and honestly
			// it is unclear if this is going to be a good idea or not.

			$Key = 0;
			$ID = NULL;
			$TableQA = NULL;
			$FieldQA = NULL;

			$Tags = Common\Datastore::FromArray($Input['TagsAll']);
			$Tags->Remap(function(int|Atlantis\Tag\Entity $T) {

				if($T instanceof Atlantis\Tag\Entity)
				return $T->ID;

				return $T;
			});

			foreach($Tags as $ID) {
				$Key += 1;

				$TableQA = "TQA{$Key}";
				$FieldQA = ":TagQA{$Key}";

				$SQL->Join(sprintf(
					'%s ON %s=%s',
					$TLink->GetAliasedTable($TableQA),
					$SQL::MkQuotedField('Main', 'UUID'),
					$SQL::MkQuotedField($TableQA, 'EntityUUID')
				));

				$SQL->Where(sprintf(
					'%s=%s',
					$SQL::MkQuotedField($TableQA, 'TagID'),
					$FieldQA
				));

				$Input[$FieldQA] = $ID;
			}

			return;
		});

		$GenTrainAnd();

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {
			case 'name-az':
				$SQL->Sort('Main.Name', $SQL::SortAsc);
			break;

			case 'name-za':
				$SQL->Sort('Main.Name', $SQL::SortDesc);
			break;

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
			'TimeCreated' => time()
		]);

		return parent::Insert($Input);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	CopyFileToUpload(Storage\File $Temp, Storage\Adaptor $Box, ?string $UUID=NULL):
	Storage\File {

		$UUID ??= Common\UUID::V7();
		$UPath = join('/', explode('-', $UUID, 2));
		$Outpath = sprintf('upl/%s/original.%s', $UPath, $Temp->GetExtension());

		////////

		$Box->Put($Outpath, $Temp->Read());
		$Copy = $Box->GetFileObject($Outpath);

		return $Copy;
	}

	static public function
	CopyUploadedToUpload(array $UploadData, Storage\Adaptor $Box, ?string $UUID=NULL):
	Storage\File {

		$UUID ??= Common\UUID::V7();
		$Ext = Common\Filesystem\Util::FileExtension($UploadData['tmp_name']) ?? 'file';
		$UPath = join('/', explode('-', $UUID, 2));
		$Outpath = sprintf('upl/%s/original.%s', $UPath, $Ext);

		////////

		$Box->Put($Outpath, file_get_contents($UploadData['tmp_name']));
		$Copy = $Box->GetFileObject($Outpath);

		return $Copy;
	}

	static public function
	Import(string $Name, Storage\File $File, ?string $UUID=NULL, ?int $UserID=NULL):
	static {

		$UserID ??= 0;
		$UUID ??= Common\UUID::V7();

		////////

		$Output = Atlantis\Media\File::Insert([
			'UUID'   => $UUID,
			'UserID' => $UserID,
			'Name'   => $Name,
			'Type'   => $File->GetType(),
			'Size'   => $File->GetSize(),
			'URL'    => $File->GetStorageURL()
		]);

		$Output->GenerateExtraFiles();

		return $Output;
	}

}
