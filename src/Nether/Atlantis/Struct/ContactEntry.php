<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('ContactUsForm', 'CUF')]
class ContactEntry
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	public int
	$TimeSent;

	#[Database\Meta\TypeVarChar(Size: 50)]
	public ?string
	$IP;

	#[Database\Meta\TypeVarChar(Size: 128)]
	public ?string
	$Name;

	#[Database\Meta\TypeVarChar(Size: 128)]
	public ?string
	$Email;

	#[Database\Meta\TypeVarChar(Size: 32)]
	public ?string
	$Phone;

	#[Database\Meta\TypeVarChar(Size: 128)]
	public ?string
	$Subject;

	#[Database\Meta\TypeText]
	public ?string
	$SentTo;

	#[Database\Meta\TypeText]
	public ?string
	$Message;

	////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeSent')]
	public Common\Date
	$DateSent;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAdminURL():
	string {

		return sprintf(
			'/dashboard/contact/view/%d',
			$this->ID
		);
	}

	public function
	GetFromString():
	string {

		if($this->Name && $this->Email)
		return sprintf('%s <%s>', $this->Name, $this->Email);

		if($this->Email)
		return $this->Email;

		if($this->Name)
		return $this->Name;

		return 'Unknown';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'newest': {
				$SQL->Sort('Main.TimeSent', $SQL::SortDesc);
				break;
			}
			case 'oldest': {
				$SQL->Sort('Main.TimeSent', $SQL::SortAsc);
				break;
			}
		}

		return;
	}

	static public function
	Insert(iterable $Input):
	?static {

		$Now = time();
		$Data = new Common\Datastore($Input);

		$Data->BlendRight([
			'TimeSent' => $Now
		]);

		return parent::Insert($Data);
	}

}
