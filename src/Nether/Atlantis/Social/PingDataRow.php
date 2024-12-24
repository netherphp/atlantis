<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Social;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

#[Database\Meta\TableClass('SocialPingData')]
class PingDataRow
extends Atlantis\Prototype {

	const
	Bluesky  = 'bluesky',
	Mastodon = 'mastodon';

	const
	Services = [
		self::Bluesky,
		self::Mastodon
	];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Common\Meta\PropertyListable]
	public int
	$TimeCreated;

	#[Database\Meta\TypeVarChar(Size: 16)]
	#[Common\Meta\PropertyListable]
	public string
	$Service;

	#[Database\Meta\TypeVarChar(Size: 32)]
	#[Common\Meta\PropertyListable]
	public string
	$Handle;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Common\Meta\PropertyListable]
	public int
	$NumFollowers;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	DescribeForPublicAPI():
	array	{

		$Output = parent::DescribeForPublicAPI();
		unset($Output['TempData']);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FetchServiceAccounts():
	Common\Datastore {

		$Output = new Common\Datastore;
		$DBM = new Database\Manager;
		$Table = static::GetTableInfo();
		$SQL = $DBM->NewVerse(static::$DBA);

		$SQL->Select($Table->Name);
		$SQL->Fields([ 'Service', 'Handle' ]);
		$SQL->Group([ 'Service', 'Handle' ]);
		$SQL->Sort([ 'Service', 'Handle' ], $SQL::SortAsc);
		$SQL->Limit(0);

		$Result = $SQL->Query();

		$Output->Import($Result->Glomp());

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		parent::FindExtendOptions($Input);

		$Input->Define([
			'Service' => NULL,
			'Handle'  => NULL,
			'Group'   => NULL
		]);

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		parent::FindExtendFilters($SQL, $Input);

		if($Input->Get('Service') !== NULL)
		$SQL->Where('Main.Service=:Service');

		if($Input->Get('Handle') !== NULL)
		$SQL->Where('Main.Handle=:Handle');

		if($Input->Get('Start') !== NULL)
		$SQL->Where('Main.TimeCreated >= :Start');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input->Get('Sort')) {
			case 'handle-az':
				$SQL->Sort(['Main.Service', 'Main.Handle'], $SQL::SortAsc);
			break;
			case 'handle-za':
				$SQL->Sort(['Main.Service', 'Main.Handle'], $SQL::SortDesc);
			break;
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
			case 'oldest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortAsc);
			break;
		}

		switch($Input->Get('Group')) {
			case 'account-newest':
				$SQL->Group('Main.Service, Main.Handle');
				$SQL->Fields('MAX(Main.ID) AS ID');
				$SQL->Fields('MAX(Main.TimeCreated) AS TimeCreated');
			break;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	static {

		$Dataset = Common\Datastore::FromArray($Input);
		$Dataset->Define([
			'TimeCreated' => time()
		]);

		return parent::Insert($Dataset);
	}

};
