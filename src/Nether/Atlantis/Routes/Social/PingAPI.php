<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Social;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class PingAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/social/history', Verb: 'HELP')]
	public function
	DataHelp():
	void {

		$this->SetPayload([
			'Inputs' => [
				'Service' => [
					'Type'  => 'string',
					'Valid' => Atlantis\Social\PingDataRow::Services
				],
				'Handle' => [
					'Type'=> 'string'
				],
				'Field'   => [
					'Type'    => 'string',
					'Default' => 'followers',
					'Valid'   => [ 'followers' ]
				]
			],
			'Outputs' => [
				'Service' => [ 'Type' => 'string' ],
				'Handle'  => [ 'Type' => 'string' ],
				'Field'   => [ 'Type' => 'string' ],
				'Data'    => [ 'Type' => 'array' ]
			]
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/social/history', Verb: 'GET')]
	public function
	DataGet():
	void {

		($this->Data)
		->FilterPush(
			'Service', Common\Filters\Misc::OneOfTheseNullable(...),
			Atlantis\Social\PingDataRow::Services
		)
		->FilterPush(
			'Handle', Common\Filters\Text::TrimmedNullable(...)
		)
		->FilterPush(
			'Field', Common\Filters\Misc::OneOfTheseFirst(...),
			[ 'followers' ]
		);

		////////

		$Filters = $this->Data->Pick('Service', 'Handle');
		$Field = match(TRUE) {
			'followers'
			=> 'NumFollowers',

			default
			=> 'NumFollowers'
		};

		if(!$Filters['Service'])
		$this->Quit(1, 'missing Service');

		if(!$Filters['Handle'])
		$this->Quit(2, 'missing Handle');

		$Filters['Page'] = 1;
		$Filters['Limit'] = 0;
		$Filters['Sort'] = 'newest';

		////////

		$Results = Atlantis\Social\PingDataRow::Find($Filters->Export());

		// chartjs.data.datasets[].data{ x, y }
		// additionally, time in miliseconds for client side.

		$Results->Remap(fn(Atlantis\Social\PingDataRow $Row)=> [
			'ID' => $Row->ID,
			'x'  => ($Row->TimeCreated * 1000),
			'y'  => $Row->{$Field}
		]);

		$this->SetPayload([
			'Service' => $Filters['Service'],
			'Handle'  => $Filters['Handle'],
			'Field'   => $this->Data->Get('Field'),
			'Data'    => $Results->Export()
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/social/overview', Verb: 'GET')]
	public function
	OverviewGet():
	void {

		$Accounts = Atlantis\Social\PingDataRow::FetchUniqueAccounts();
		$Overview = new Common\Datastore;

		////////

		$Accounts->Each(function(object $A) use($Overview) {

			$Latest = Atlantis\Social\PingDataRow::Find([
				'Service' => $A->Service,
				'Handle'  => $A->Handle,
				'Limit'   => 1,
				'Sort'    => 'newest'
			]);

			$Row = $Latest->Current();
			$Followers = 0;

			if($Row) {
				$Followers = $Row->NumFollowers;
			}

			////////

			$Service = Atlantis\Social\Service::FromFactory($A->Service);
			$Service->SetHandle($A->Handle);

			$Overview->Push([
				'Service'   => $A->Service,
				'Name'      => $Service->GetName(),
				'Icon'      => $Service->GetIcon(),
				'Handle'    => $A->Handle,
				'URL'       => $Service->GetURL(),
				'Followers' => $Followers
			]);

			return;
		});

		$this->SetPayload($Overview->Export());

		return;
	}

};
