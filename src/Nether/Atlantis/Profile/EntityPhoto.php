<?php

namespace Local\AbuseRegistry;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('RegistryEntityPhotos', 'REP')]
class EntityPhoto
extends Atlantis\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('RegistryEntities', 'ID', Delete: TRUE)]
	public int
	$EntityID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Uploads', 'ID', Delete: TRUE)]
	public int
	$PhotoID;

	////////

	public Entity
	$Entity;

	public Atlantis\Media\File
	$Photo;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('UP_ID'))
		$this->Photo = Atlantis\Media\File::FromPrefixedDataset($Args->Input, 'UP_');

		if($Args->InputHas('RE_ID'))
		$this->Entity = Entity::FromPrefixedDataset($Args->Input, 'RE_');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	JoinExtendTables(Database\Verse $SQL, string $JAlias='Main', ?string $TPre=NULL):
	void {

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);
		$JAlias = $Table->GetPrefixedAlias($JAlias);

		Entity::JoinMainTables($SQL, $JAlias, 'EntityID', $TPre);
		Atlantis\Media\File::JoinMainTables($SQL, $JAlias, 'PhotoID', $TPre);

		return;
	}

	static public function
	JoinExtendFields(Database\Verse $SQL, ?string $TPre=NULL):
	void {

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);

		Entity::JoinMainFields($SQL, $TPre);
		Atlantis\Media\File::JoinMainFields($SQL, $TPre);

		return;
	}


	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['EntityID'] ??= NULL;
		$Input['PhotoID'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['EntityID'] !== NULL)
		$SQL->Where('Main.EntityID=:EntityID');

		if($Input['PhotoID'] !== NULL)
		$SQL->Where('Main.PhotoID=:PhotoID');

		return;
	}

}
