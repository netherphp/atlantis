<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('TrafficRows', 'TR')]
class TrafficRow
extends Atlantis\Prototype {

	#[Database\Meta\TypeChar(Size: 128)]
	#[Database\Meta\FieldIndex]
	public string
	$Hash;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\FieldIndex]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Users', 'ID')]
	public int
	$UserID;

	#[Database\Meta\TypeChar(Size: 46)]
	#[Database\Meta\FieldIndex]
	public string
	$IP;

	#[Database\Meta\TypeChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public string
	$URL;

	#[Database\Meta\TypeChar(Size: 100)]
	#[Database\Meta\FieldIndex]
	public string
	$Domain;

	#[Database\Meta\TypeChar(Size: 100)]
	#[Database\Meta\FieldIndex]
	public string
	$Path;

	#[Database\Meta\TypeChar(Size: 100)]
	#[Database\Meta\FieldIndex]
	public string
	$Query;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Hash'] ??= NULL;
		$Input['Since'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['Hash'] !== NULL)
		$SQL->Where('Main.Hash=:Hash');

		if($Input['Since'] !== NULL)
		$SQL->Where('Main.TimeCreated > :Since');

		if($Input['Before'] !== NULL)
		$SQL->Where('Main.TimeCreated < :Before');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		return;
	}

	static public function
	Insert(iterable $Input):
	static {

		$Now = new Common\Date;
		$Input = new Common\Datastore($Input);

		$Input->BlendRight([
			'TimeCreated' => $Now->GetUnixtime()
		]);

		return parent::Insert($Input);
	}

}
