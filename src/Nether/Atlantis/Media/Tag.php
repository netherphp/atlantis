<?php

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

#[Database\Meta\TableClass('Tags', 'T')]
class Tag
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$UUID;

	#[Database\Meta\TypeChar(Size: 8, Default: 'tag', Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$Type;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public int
	$Enabled;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeVarChar(Size: 100, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 100, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$Name;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		return;
	}

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['NameLike'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['NameLike'] !== NULL) {
			$SQL->Where('Main.Name LIKE :NameLikeLike');
			$Input['NameLikeLike'] = "%%{$Input['NameLike']}%%";
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Common\Datastore($Input);
		$Input->BlendLeft([
			'TimeCreated' => time(),
			'UUID'        => Common\UUID::V7(),
			'Alias'       => NULL,
			'Name'        => NULL
		]);

		////////

		if(!$Input['Name'])
		throw new Exception('tag needs a name');

		////////

		if(!$Input['Alias'])
		$Input['Alias'] = Common\Datafilters::PathableKey($Input['Name']);

		if(!$Input['Alias'])
		$Input['Alias'] = Common\UUID::V7();

		////////

		return parent::Insert($Input);
	}

}
