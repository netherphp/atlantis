<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class Prototype
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36)]
	#[Database\Meta\FieldIndex]
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

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Common\Datastore($Input);

		$Input->BlendRight([
			'UUID' => ($Input['UUID'] ?: Common\UUID::V7())
		]);

		return parent::Insert($Input);
	}

}
