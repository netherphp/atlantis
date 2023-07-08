<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class Prototype
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	#[Common\Meta\PropertyListable]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	public string
	$UUID;

	////////

	protected Database\Struct\PrototypeFindResult
	$TagLinks;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Drop():
	static {

		parent::Drop();

		Tag\EntityLink::DeleteByEntity($this->UUID);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	FetchTagLinks():
	Database\Struct\PrototypeFindResult {

		$Result = Tag\EntityLink::Find([
			'EntityUUID' => $this->UUID
		]);

		return $Result;
	}

	public function
	GetTagLinks():
	Common\Datastore {

		if(!isset($this->TagLinks))
		$this->TagLinks = $this->FetchTagLinks();

		return $this->TagLinks;
	}

	public function
	GetTags():
	Common\Datastore {

		$Links = $this->GetTagLinks();

		return $Links->Map(
			fn(Tag\EntityLink $Link)
			=> $Link->Tag
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	DescribeForPublicAPI():
	array {

		return (array)$this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetByUUID(string $UUID):
	?static {

		return parent::GetByField('UUID', $UUID);
	}


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {



		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if(isset($Input['UUID'])) {
			if(is_array($Input['UUID']))
			$SQL->Where('Main.UUID IN(:UUID)');

			else
			$SQL->Where('Main.UUID=:UUID');
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
			'UUID' => ($Input['UUID'] ?: Common\UUID::V7())
		]);

		return parent::Insert($Input);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	MapForPublicAPI(self $Inst):
	array {

		return $Inst->DescribeForPublicAPI();
	}

}
