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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Drop():
	static {

		parent::Drop();
		Atlantis\Media\TagLink::DeleteByEntity($this->UUID);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetTagLinks():
	Common\Datastore {

		$Result = Media\TagLink::Find([
			'EntityUUID' => $this->UUID
		]);

		return $Result;
	}

	public function
	GetTags():
	Common\Datastore {

		$Links = $this->GetTagLinks();

		return $Links->Map(
			fn(Media\TagLink $Link)
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

		$Output = new Common\Datastore($Input);

		$Output->BlendRight([
			'UUID' => ($Output['UUID'] ?? Common\UUID::V7())
		]);

		return static::Insert($Output);
	}

}
