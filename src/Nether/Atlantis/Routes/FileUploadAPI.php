<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes;

use FileEye;
use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Storage;

use Exception;
use SplFileInfo;
use Nether\Atlantis\Plugin\Interfaces\Engine\FileUploadInterface;

################################################################################
################################################################################

#[Common\Meta\Date('2024-06-21')]
#[Common\Meta\Info('This is to replace UploadAPI with sanity and plugin support.')]
class FileUploadAPI
extends Atlantis\ProtectedAPI {

	const
	QuitMsg = [
		1  => 'no files provided',
		2  => 'not a chunked upload',
		3  => 'upload error (usually exceeded upload max filesize)',
		4  => 'no Temp storage defined',
		5  => 'no Default storage defined',
		6  => 'how are you on a middle chunk without having uuid?',
		7  => 'cannot finalise without the uuid',
		8  => 'cannot finalise without the path',
		9  => 'cannot finalise without the type',
		10 => 'path for completed temp file invalid'
	];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2024-06-21')]
	protected ?Storage\Adaptor
	$Temp;

	#[Common\Meta\Date('2024-06-21')]
	protected ?Storage\Adaptor
	$Storage;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2024-06-21')]
	public function
	OnReady(?Common\Datastore $Input):
	void {

		parent::OnReady($Input);

		////////

		$this->Temp = $this->App->Storage->Location('Temp');
		$this->Storage = $this->App->Storage->Location('Default');

		if(!isset($this->Temp))
		$this->Quit(4);

		if(!isset($this->Storage))
		$this->Quit(5);

		////////

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/file/upload', Verb: 'UPCONFIG')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Common\Meta\Info('Provides info for the front end to configure itself.')]
	#[Common\Meta\Date('2024-06-21')]
	public function
	OnUploadConfig():
	void {

		$UploadMax = Common\Units\Bytes::FromReadable(ini_get('upload_max_filesize'));
		$UploadChunk = Common\Units\Bytes::FromInt(1024 * 1024);

		$this->SetPayload([
			'UploadName'   => 'File',
			'UploadType'   => 'chunked',
			'UploadMax'    => $UploadMax->GetBytes(),
			'UploadChunk'  => $UploadChunk->GetBytes(),

			'UploadMethod' => 'UPCHUNK',
			'UploadURL'    => '/api/file/upload',

			'FinalMethod'  => 'UPFINAL',
			'FinalURL'     => '/api/file/upload'
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/file/upload', Verb: 'UPCHUNK')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Common\Meta\Info('Handle each incoming chunk to assemble a full file.')]
	#[Common\Meta\Date('2024-06-21')]
	public function
	OnUploadChunk():
	void {

		($this->Data)
		->Type(Common\Filters\Text::TrimmedNullable(...))
		->UUID(Common\Filters\Text::UUID(...));

		$Upload = $this->Request->File->Get('File');
		$Range = Avenue\Headers\HttpContentRange::FromEnv();
		$TempOutput = NULL;
		$TempInput = NULL;
		$UUID = NULL;
		$Type = NULL;
		$Name = NULL;
		$Size = NULL;

		////////

		if(!$Upload)
		$this->Quit(1);

		if(!$Range)
		$this->Quit(2);

		if($Upload['size'] === 0)
		$this->Quit(3);

		////////

		$UUID = $this->Data->UUID ?? Common\UUID::V7();
		$Type = $this->Data->Type ?? 'default';

		$TempInput = $Upload['tmp_name'];
		$TempOutput = $this->DetermineTempFile($UUID);

		$Name = $Upload['name'];
		$Size = $Upload['size'];
		$File = NULL;

		////////

		if($Range->Begin === 0) {
			$this->ChunkWriteFirst($UUID, $TempInput, $TempOutput);
		}

		if($Range->Begin !== 0) {
			if(!$this->Data->UUID)
			$this->Quit(6);

			$this->ChunkWriteNext($UUID, $TempInput, $TempOutput);
		}

		if($Range->End === $Range->Total) {
			$File = $this->ChunkWriteFinal($UUID, $TempOutput);
		}

		////////

		unlink($TempInput);

		$this->SetPayload([
			'Type'  => $Type,
			'UUID'  => $UUID,
			'Name'  => $Name,
			'Size'  => $Size,
			'Range' => $Range,
			'Path'  => $File ? $File->Path : NULL
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/file/upload', Verb: 'UPFINAL')]
	#[Atlantis\Meta\RouteAccessTypeUser]
	#[Common\Meta\Info('Officially add the file to the tracking system and ask any plugins if they wanna do anything with it.')]
	#[Common\Meta\Date('2024-06-21')]
	public function
	OnUploadDone():
	void {

		($this->Data)
		->UUID(Common\Filters\Text::UUID(...))
		->Path(Common\Filters\Text::TrimmedNullable(...))
		->Type(Common\Filters\Text::TrimmedNullable(...))
		->Name(Common\Filters\Text::TrimmedNullable(...));

		($this->Data)
		->FilterPush(
			'Tags',
			Common\Filters\Lists::CommaOfNullable(...),
			Common\Filters\Numbers::IntType(...)
		);

		////////

		$Type = $this->Data->Type ?: 'default';
		$UUID = $this->Data->UUID;
		$Path = $this->Data->Path;
		$Name = $this->Data->Name;
		$Outpath = NULL;
		$File = NULL;
		$Done = NULL;
		$Entity = NULL;
		$Tags = $this->Data->Tags;

		if(!$UUID)
		$this->Quit(7);

		if(!$Path)
		$this->Quit(8);

		if(!$Type)
		$this->Quit(9);

		if(!$this->Temp->Exists($Path))
		$this->Quit(10);

		// move the file to the final resting place.

		$File = $this->Temp->GetFileObject($Path);

		$Outpath = sprintf(
			'upl/%s/original.%s',
			join('/', explode('-', $UUID, 2)),
			$File->GetExtension()
		);

		$this->Storage->Put($Outpath, $File->Read());
		$File->DeleteParentDirectory();

		// track the file in the database.

		$Done = $this->Storage->GetFileObject($Outpath);

		$Entity = Atlantis\Media\File::Insert([
			'UUID'     => $UUID,
			'UserID'   => $this->User->ID,
			'Name'     => $Name,
			'Type'     => $Done->GetType(),
			'MimeType' => $Done->ReadMimeType(),
			'Size'     => $Done->GetSize(),
			'URL'      => $Done->GetStorageURL()
		]);

		$Entity->Update($Entity->Patch([
			'ExtraData'=> [ 'OriginalName'=> $Name ] ]
		));

		$Entity->GenerateExtraFiles();

		////////

		// associate tags that were submitted along the way.

		if(isset($Tags) && is_array($Tags)) {
			foreach($Tags as $TagID)
			Atlantis\Media\FileTagLink::InsertByPair($TagID, $Entity->UUID);

			unset($TagID);
		}

		////////

		// allow plugins to do follow up work.
		// for example to upload a blog header our upload form should have
		// set Type to 'blog-header' and that library would then include
		// a plugin that will check for blog-header and finish updating
		// whatever needs to be updated with references to this new upload.

		$this->OnUploadDonePlugins($Type, $Entity, $this->Data);
		$this->OnUploadDoneRelateProfile($Type, $Entity, $this->Data);
		$this->OnUploadDoneRelateTag($Type, $Entity, $this->Data);

		//$this->SetGoto($Entity->GetPageURL());
		$this->SetPayload($Entity->DescribeForPublicAPI());

		return;
	}

	#[Common\Meta\Date('2024-06-22')]
	protected function
	OnUploadDonePlugins(string $Type, Atlantis\Media\File $Entity, Common\Datafilter $Data):
	void {

		($this->App->Plugins)
		->GetInstanced(FileUploadInterface::class)
		->Filter(
			fn(FileUploadInterface $P)
			=> $P->WillHandleUpload($Type, $Entity, $Data)
		)
		->Each(
			fn(FileUploadInterface $P)
			=> $P->OnHandleUpload($Type, $Entity, $Data)
		);

		return;
	}

	#[Common\Meta\Date('2024-11-08')]
	protected function
	OnUploadDoneRelateProfile(string $Type, Atlantis\Media\File $File, Common\Datafilter $Data):
	void {

		$ParentUUID = $Data->Get('ParentUUID');
		$ParentType = $Data->Get('ParentType');
		$ParentClass = NULL;
		$ParentEntity = NULL;

		////////

		if(!$ParentUUID || !$ParentType)
		return;

		$ParentClass = Atlantis\Struct\EntityRelationship::TypeClass($ParentType);
		$ParentEntity = $ParentClass::GetByUUID($ParentUUID);

		if(!$ParentEntity)
		return;

		////////

		Atlantis\Struct\EntityRelationship::InsertByPair(
			$ParentClass::EntType,
			$ParentUUID,
			$File::EntType,
			$File->UUID
		);

		return;
	}

	#[Common\Meta\Date('2024-11-08')]
	protected function
	OnUploadDoneRelateTag(string $Type, Atlantis\Media\File $File, Common\Datafilter $Data):
	void {

		if($Type !== 'tagphoto')
		return;

		////////

		$TagID = Common\Filters\Numbers::IntType($Data->Get('TagID'));
		$Tag = Atlantis\Tag\Entity::GetByID($TagID);

		if(!$Tag)
		return;

		////////

		Atlantis\Tag\EntityPhoto::Insert([
			'EntityID' => $Tag->ID,
			'PhotoID'  => $File->ID
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	ChunkWriteFirst(string $UUID, string $Infile, string $Outfile):
	void {

		$this->Temp->Put(
			$Outfile,
			file_get_contents($Infile)
		);

		return;
	}

	protected function
	ChunkWriteNext(string $UUID, string $Infile, string $Outfile):
	void {

		$this->Temp->Append(
			$Outfile,
			file_get_contents($Infile)
		);

		return;
	}

	public function
	ChunkWriteFinal(string $UUID, string $Tempfile):
	Storage\File {

		$Mime = $this->Temp->ReadMimeType($Tempfile);
		$Ext = $this->DetermineMimeExt($Mime);

		$Outfile = sprintf(
			'%s/original.%s',
			sprintf('upl/%s', $UUID),
			$Ext
		);

		////////

		$this->Temp->Rename($Tempfile, $Outfile);
		$this->Temp->Chmod($Outfile, 0666);

		$Source = $this->Temp->GetFileObject($Outfile);

		////////

		return $Source;
	}

	protected function
	DetermineTempFile(string $UUID):
	string {

		$Path = Common\Filesystem\Util::Pathify(
			'upl', sprintf('tmp-%d-%s', $this->User->ID, $UUID)
		);

		return $Path;
	}

	protected function
	DetermineMimeExt(string $MimeType):
	string {

		$Type = new FileEye\MimeMap\Type($MimeType);
		$Ext = $Type->GetDefaultExtension();

		return $Ext ?: 'unknown';
	}

};
