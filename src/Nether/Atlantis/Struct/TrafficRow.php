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

	#[Database\Meta\TypeChar(Size: 128)]
	#[Database\Meta\FieldIndex]
	public string
	$Visitor;

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

	#[Database\Meta\TypeChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public string
	$Path;

	#[Database\Meta\TypeChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public string
	$Query;

	#[Database\Meta\TypeVarChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public ?string
	$FromURL;

	#[Database\Meta\TypeChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public ?string
	$FromDomain;

	#[Database\Meta\TypeChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public ?string
	$FromPath;

	#[Database\Meta\TypeChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public ?string
	$FromQuery;

	#[Database\Meta\TypeVarChar(Size: 255)]
	public ?string
	$UserAgent;

	////////

	protected int
	$GroupCount = 0;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetGroupCount():
	int {

		return $this->GroupCount;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Since'] ??= NULL;
		$Input['Before'] ??= NULL;
		$Input['Domain'] ??= NULL;

		$Input['Hash'] ??= NULL;
		$Input['PathStart'] ??= NULL;

		$Input['Group'] ??= NULL;

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

		if($Input['PathStart'] !== NULL) {
			$Input[':PathStartLike'] = "{$Input['PathStart']}%";
			$SQL->Where('Main.Path LIKE :PathStartLike');
		}

		if($Input['Domain'] !== NULL) {
			if($Input['Domain'] === TRUE)
			$SQL->Where('Main.Domain IS NOT NULL');
			else
			$SQL->Where('Main.Domain=:Domain');
		}

		if($Input['Group'] !== NULL) {
			if($Input['Group'] === 'path')
			$SQL->Group('Main.Path');

			if($Input['Group'] === 'visitor')
			$SQL->Group('Main.Visitor');

			if($Input['Group'] === 'domain')
			$SQL->Group('Main.Domain');

			if($Input['Group'] === 'useragent')
			$SQL->Group('Main.UserAgent');

			if($Input['Group'] === 'page')
			$SQL->Group([ 'Main.Domain', 'Main.Path' ]);

			$SQL->Fields([ 'GroupCount'=> 'COUNT(*) AS GroupCount' ]);
		}

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'group-count-az':
				$SQL
				->Sort('GroupCount', $SQL::SortAsc)
				->Sort('Main.Domain', $SQL::SortAsc)
				->Sort('Main.Path', $SQL::SortAsc);
			break;
			case 'group-count-za':
				$SQL
				->Sort('GroupCount', $SQL::SortDesc)
				->Sort('Main.Domain', $SQL::SortAsc)
				->Sort('Main.Path', $SQL::SortAsc);
			break;
		}

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
