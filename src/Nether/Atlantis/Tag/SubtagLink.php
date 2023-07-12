<?php

namespace Nether\Atlantis\Tag;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

use Exception;

class SubtagLink
extends EntityLink {

	#[Atlantis\Meta\TagEntityProperty('subtag')]
	public Entity
	$Subtag;

	protected function
	OnReadyEntity(Common\Prototype\ConstructArgs $Args):
	void {

		//Common\Dump::Var($Args->Input, TRUE);

		if($Args->InputHas('T_T_ID'))
		$this->Subtag = Entity::FromPrefixedDataset(
			$Args->Input, 'T_T_'
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	JoinExtendTables(Database\Verse $SQL, string $JAlias='Main', ?string $TPre=NULL):
	void {

		parent::JoinExtendTables($SQL, $JAlias, $TPre);

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);
		$JAlias = $Table->GetPrefixedAlias($JAlias);

		Entity::JoinMainTables($SQL, $JAlias, 'EntityUUID', "T{$TPre}");
		Entity::JoinExtendTables($SQL, "T{$TPre}", "T{$TPre}");

		return;
	}

	static public function
	JoinExtendFields(Database\Verse $SQL, ?string $TPre=NULL):
	void {

		parent::JoinExtendFields($SQL, $TPre);

		$Table = static::GetTableInfo();
		$TPre = $Table->GetPrefixedAlias($TPre);

		Entity::JoinMainFields($SQL, "T{$TPre}");
		Entity::JoinExtendFields($SQL, "T{$TPre}");

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendSorts($SQL, $Input);

		switch($Input['Sort']) {

		}

		return;
	}

}
