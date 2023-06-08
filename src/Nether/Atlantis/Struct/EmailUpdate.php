<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\Email;
use Nether\Surface;
use Nether\User;

use Database\Verse;
use Nether\Common\Datastore;

#[Database\Meta\TableClass('UserEmailUpdates')]
class EmailUpdate
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Users', 'ID')]
	public int
	$EntityID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeVarChar(Size: 255)]
	public string
	$Email;

	#[Database\Meta\TypeChar(Size: 64)]
	public string
	$Code;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Send(bool $Activate=FALSE):
	static {

		$Template = 'email/user-email-update';
		$Subject = 'Email Change Confirmation';

		if($Activate) {
			$Template = 'email/user-email-activate';
			$Subject = 'Activate Your Account';
		}

		////////

		$Scope = [ 'Update'=> $this ];
		$Generator = new Surface\Engine(Surface\Library::$Config);
		$Content = $Generator->GetArea($Template, $Scope);
		unset($Generator);

		////////

		$Send = new Email\Outbound;
		$Send->To->Push($this->Email);
		$Send->Subject = $Subject;
		$Send->Content = $Content;
		$Send->Send();

		return $this;
	}

	public function
	GetConfirmURL():
	string {

		return new Atlantis\WebURL(sprintf(
			'/dashboard/settings/email?confirm=%s',
			Common\Datafilters::Base64Encode($this->Code)
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
		$DBM = new Database\Manager;

		$Result = (
			$DBM->NewVerse(static::$DBA)
			->Delete($Table->Name)
			->Where('EntityID=:EntityID')
			->Query([ 'EntityID'=> $EntityID ])
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['EntityID'] ??= NULL;
		$Input['Code'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['EntityID'] !== NULL)
		$SQL->Where('`Main`.`EntityID`=:EntityID');

		if($Input['Code'] !== NULL)
		$SQL->Where('`Main`.`Code`=:Code');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

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
			'Code'        => static::Generate()
		]);

		return parent::Insert($Input);
	}

}