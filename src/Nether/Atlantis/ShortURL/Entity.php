<?php

namespace Nether\Atlantis\ShortURL;

use Nether\Atlantis;
use Nether\Database;
use Nether\Common;

#[Database\Meta\TableClass('ShortURLs')]
class Entity
extends Atlantis\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36, Default: NULL)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyListable]
	public string
	$UUID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Database\Meta\FieldIndex]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$CountHits;

	#[Database\Meta\TypeVarChar(Size: 256, Default: NULL)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	#[Common\Meta\PropertyListable]
	public string
	$URL;

	#[Database\Meta\TypeVarChar(Size: 256, Default: NULL)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	#[Common\Meta\PropertyListable]
	public ?string
	$Title;

	#[Database\Meta\TypeVarChar(Size: 256, Default: NULL)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	#[Common\Meta\PropertyListable]
	public ?string
	$Alias;

	////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	#[Common\Meta\PropertyListable]
	public Common\Date
	$DateCreated;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	DescribeForPublicAPI():
	array {

		$Data = parent::DescribeForPublicAPI();
		$Data['ShortURL'] = $this->GetShortURL()->Get();

		return $Data;
	}

	public function
	BumpHitCount(int $Inc=1):
	static {

		$this->Update([
			'CountHits' => ($this->CountHits + $Inc)
		]);

		return $this;
	}

	public function
	GetShortURL():
	Atlantis\WebURL {

		return new Atlantis\WebURL(sprintf(
			'/link/%s',
			$this->Alias
		));
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
			case 'oldest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortAsc);
			break;
			case 'url-az':
				$SQL->Sort('Main.URL', $SQL::SortAsc);
			break;
			case 'url-za':
				$SQL->Sort('Main.URL', $SQL::SortDesc);
			break;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	static {

		$Now = Common\Date::Unixtime();
		$UUID = Common\UUID::V7();
		$Data = new Common\Datastore($Input);
		$Alias = NULL;
		$Old = NULL;

		$Try = 0;
		$CharLen = 4;

		////////

		$Old = Atlantis\ShortURL\Entity::GetByField('URL', $Input['URL']);

		if($Old)
		return $Old;

		////////

		while(TRUE) {
			$Alias = substr(hash('sha512', Common\UUID::V4()), 0, $CharLen);
			$Old = Atlantis\ShortURL\Entity::GetByField('Alias', $Alias);

			if(!$Old)
			break;

			$Try += 1;

			if($Try > 16) {
				$CharLen += 1;
				$Try = 1;
			}
		}

		////////

		$Data->BlendRight([
			'UUID'        => $UUID,
			'TimeCreated' => $Now,
			'URL'         => NULL,
			'Alias'       => $Alias
		]);

		return parent::Insert($Data);
	}

}
