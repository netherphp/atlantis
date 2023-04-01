<?php

namespace Nether\Atlantis\Page;

use Nether\Common;
use Nether\Database;

use Exception;

#[Database\Meta\TableClass('Pages', 'P')]
class Entity
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: NULL)]
	#[Database\Meta\ForeignKey('Users', 'ID')]
	public int
	$UserID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeUpdated;

	#[Database\Meta\TypeVarChar(Size: 64, Default: NULL)]
	#[Database\Meta\NullifyEmptyValue]
	public ?string
	$Alias;

	#[Database\Meta\TypeVarChar(Size: 64, Default: NULL)]
	public string
	$Title;

	#[Database\Meta\TypeVarChar(Size: 12, Default: 'html')]
	public string
	$Editor;

	#[Database\Meta\TypeText]
	public string
	$Content;

	////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	#[Common\Meta\PropertyFactory('FromTime', 'TimeUpdated')]
	public Common\Date
	$DateUpdated;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Now = time();
		$Input = new Common\Datastore($Input);

		$Input->BlendRight([
			'UserID'      => NULL,
			'Title'       => NULL,
			'Alias'       => NULL,
			'TimeCreated' => $Now,
			'TimeUpdated' => $Now
		]);

		////////

		if(!$Input['Title'])
		throw new Exception('Page must have a title.');

		if(!$Input['Alias'])
		$Input['Alias'] = Common\Datafilters::PathableKey($Input['Title']);

		////////

		return parent::Insert($Input);
	}

}
