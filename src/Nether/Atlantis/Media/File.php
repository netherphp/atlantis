<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Storage;

use Exception;

#[Database\Meta\TableClass('Uploads', 'UP')]
#[Database\Meta\InsertUpdate]
#[Database\Meta\InsertReuseUnique]
class File
extends Atlantis\Prototype {

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public Common\Date
	$DateCreated;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->DateCreated = Common\Date::FromTime($this->TimeCreated);

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
	GetFile():
	Storage\File {

		$File = Storage\Manager::GetFileByURL($this->URL);

		return $File;
	}

	public function
	GetSizeReadable():
	string {

		$Bytes = new Common\Units\Bytes($this->Size);

		return $Bytes->Get();
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
