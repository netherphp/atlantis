<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Blob;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

#[Database\Meta\TableClass('AtlBlobGroups', 'AtlBG')]
class Group
extends Atlantis\Prototype {

	const
	EntType = 'Blob.Group';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeVarChar(Size: 255)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public string
	$Title;

	////////

	protected Common\Datastore
	$CachedItems;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	DescribeForPublicAPI():
	array {

		return [
			'ID'       => $this->ID,
			'UUID'     => $this->UUID,
			'Title'    => $this->Title
		];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetTitle():
	string {

		return $this->Title;
	}

	public function
	FetchItems():
	Database\ResultSet {

		return Entity::Find([
			'GroupID' => $this->ID,
			'Limit'   => 0,
			'Sort'    => 'title-az'
		]);
	}

	public function
	GetItems():
	Common\Datastore {

		if(!isset($this->CachedItems))
		$this->CachedItems = Common\Datastore::FromArray(
			$this->FetchItems()->Export()
		);

		return $this->CachedItems->Copy();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetEditAttributes():
	Common\Datastore {

		$Output = new Common\Datastore([
			'data-atl-bgrp-cmd'  => 'edit',
			'data-atl-bgrp-uuid' => $this->UUID
		]);

		return $Output;
	}

	public function
	GetEditAttributesForHTML():
	string {

		$Attribs = $this->GetEditAttributes();

		$Attribs->RemapKeyValue(
			fn(string $K, string $V)
			=> sprintf('%s="%s"', $K, $V)
		);

		return $Attribs->Join(' ');
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input->Define([
			'UUIDPrefix' => NULL
		]);

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['UUIDPrefix'] !== NULL) {
			$Input[':UUIDPrefixLike'] = sprintf('%s%%', $Input['UUIDPrefix']);
			$SQL->Where('Main.UUID LIKE :UUIDPrefixLike');
		}

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'title-az':
				$SQL->Sort('Main.Title', $SQL::SortAsc);
			break;
			case 'title-za':
				$SQL->Sort('Main.Title', $SQL::SortDesc);
			break;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Touch(string $UUID, string $Title):
	static {

		$Grp = static::GetByUUID($UUID);

		////////

		if(!$Grp)
		$Grp = static::Insert([
			'UUID'  => $UUID,
			'Title' => $Title
		]);

		////////

		return $Grp;
	}

};
