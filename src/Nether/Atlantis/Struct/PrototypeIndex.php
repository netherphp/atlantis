<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

use Exception;
use Nether\Atlantis\Struct\EntityRelationship;

################################################################################
################################################################################

#[Database\Meta\TableClass('PrototypeIndex')]
#[Database\Meta\InsertIgnore]
#[Database\Meta\InsertReuseUnique]
#[Database\Meta\MultiFieldIndex([ 'Type', 'UUID' ], Unique: TRUE)]
class PrototypeIndex
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36)]
	public string
	$UUID;

	#[Database\Meta\TypeChar(Size: 36)]
	public string
	$Type;

	////////////////
	////////////////

	protected mixed
	$Object;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Info('Fetch the object referenced from the database..')]
	public function
	Fetch():
	mixed {

		$Class = EntityRelationship::TypeClass($this->Type);

		if(!$Class)
		throw new Common\Error\FormatInvalid('FQCN');

		////////

		$Object = $Class::GetByUUID($this->UUID);

		if(!$Object)
		throw new Common\Error\RequiredDataMissing('Valid Object');

		////////

		return $Object;
	}

	#[Common\Meta\Info('Ftch the object referenced, cached locally.')]
	public function
	Get():
	mixed {

		if(!isset($this->Object))
		$this->Object = $this->Fetch();

		return $this->Object;
	}

	////////////////////////////////////////////////////////////////
	// STATIC API //////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Opt = Common\Datastore::FromStackMerged(
			[ 'Type' => NULL, 'UUID' => NULL ],
			$Input
		);

		////////

		if(!$Opt['Type'])
		throw new Common\Error\RequiredDataMissing('Type', 'string');

		if(!$Opt['UUID'])
		throw new Common\Error\RequiredDataMissing('UUID', 'string');

		////////

		return parent::Insert($Opt);
	}

	static public function
	InsertByPair(string $Type, string $UUID):
	?static {

		return static::Insert([
			'Type' => $Type,
			'UUID' => $UUID
		]);
	}

	static public function
	InsertFor(Atlantis\Prototype $Object):
	?static {

		$Const = sprintf('%s::EntType', $Object::class);

		if(!defined($Const))
		throw new Common\Error\RequiredDataMissing(
			$Const, 'const<string>'
		);

		////////

		return static::InsertByPair(
			constant($Const),
			$Object->UUID
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FetchTypeForUUID(string $UUID):
	?string {

		$Row = static::GetByField('UUID', $UUID);

		if(!$Row)
		return NULL;

		return $Row->Type;
	}

	static public function
	FetchObjectForUUID(string $UUID, bool $ThrowIfBroken=FALSE):
	?object {

		$Row = NULL;
		$Obj = NULL;
		$Err = NULL;

		////////

		$Row = static::GetByField('UUID', $UUID);

		if(!$Row)
		return NULL;

		try { $Obj = $Row->Fetch(); }
		catch(Common\Error\RequiredDataMissing $Err) {
			if(!$ThrowIfBroken)
			return NULL;

			throw $Err;
		}

		////////

		return $Obj;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DeleteUUID(string $UUID):
	void {

		$Row = static::GetByField('UUID', $UUID);

		////////

		if($Row)
		$Row->Drop();

		return;
	}

	static public function
	UpdateUUID(string $Old, string $New):
	void {

		$Row = static::GetByField('UUID', $Old);

		////////

		if($Row)
		$Row->Update([ 'UUID'=> $New ]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Deprecated('2026-06-12', 'Use DeleteUUID')]
	static public function
	DeleteByUUID(string $UUID):
	void {

		static::DeleteUUID($UUID);
		return;
	}

};
