<?php

namespace Nether\Atlantis\Struct;
use Nether;

use Nether\Surface;

use Nether\Database\Verse;
use Nether\Object\Datastore;
use Nether\Common\Datafilters;

#[Nether\Database\Meta\TableClass('UserLoginResets')]
class LoginReset
extends Nether\Database\Prototype {

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Nether\Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeCreated;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Nether\Database\Meta\ForeignKey('Users', 'ID')]
	public int
	$EntityID;

	#[Nether\Database\Meta\TypeChar(Size: 64)]
	public string
	$Code;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Send():
	static {

		$User = Nether\User\Entity::GetByID($this->EntityID);

		$Scope = [ 'Reset'=> $this ];
		$Generator = new Surface\Engine(Surface\Library::Config());
		$Content = $Generator->GetArea('email/user-login-reset', $Scope);
		unset($Generator);

		$Send = new Nether\Email\Outbound;
		$Send->To->Push($User->Email);
		$Send->Subject = 'Login Reset Information';
		$Send->Content = $Content;
		$Send->Send();

		return $this;
	}

	public function
	GetURL():
	string {

		$Encoded = Datafilters::Base64Encode(json_encode([
			'ID'   => $this->ID,
			'Code' => $this->Code
		]));

		return new Nether\Atlantis\WebURL(sprintf(
			'/login/reset?code=%s',
			$Encoded
		));
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Generate(int $Len=32):
	string {

		$Iter = NULL;
		$Output = '';

		for($Iter = 0; $Iter < $Len; $Iter++)
		$Output .= match(random_int(1, 3)) {
			1 => chr(random_int(65, 90)),  // A-Z
			2 => chr(random_int(97, 122)), // a-z
			3 => (string)random_int(0, 9)  // 0-9
		};

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DropForEntityID(int $EntityID):
	void {

		$Table = static::GetTableInfo();

		$Result = (
			(new Nether\Database)
			->NewVerse()
			->Delete($Table->Name)
			->Where('EntityID=:EntityID')
			->Query([ 'EntityID'=> $EntityID ])
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Datastore $Input):
	void {

		$Input->EntityID ??= NULL;
		$Input->Code ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Verse $SQL, Datastore $Input):
	void {

		if($Input->EntityID !== NULL)
		$SQL->Where('Main.EntityID=:EntityID');

		if($Input->Code !== NULL)
		$SQL->Where('Main.Code=:Code');

		return;
	}

	static protected function
	FindExtendSorts(Verse $SQL, Datastore $Input):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Nether\Object\Prototype($Input, [
			'TimeCreated' => time()
		]);

		return parent::Insert((array)$Input);
	}

}