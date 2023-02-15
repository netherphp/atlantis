<?php

namespace Nether\Atlantis;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

class Prototype
extends Database\Prototype {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
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

	static public function
	GetByUUID(string $UUID):
	?static {

		return parent::GetByField('UUID', $UUID);
	}

}
