<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

class UsersWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/users/list')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleList():
	void {

		($this->Data)
		->Q(Common\Filters\Text::TrimmedNullable(...))
		->AccessType(Common\Filters\Numbers::IntNullable(...))
		->Sort(Common\Filters\Text::TrimmedNullable(...));

		////////

		$Filters = [ ];

		if($this->Data->Q) {
			$Filters['Search'] = $this->Data->Q;
			$Filters['SearchAlias'] = TRUE;
			$Filters['SearchEmail'] = TRUE;
		}

		if($this->Data->AccessType) {
			$Filters['WithAccessType'] = $this->Data->AccessType;
		}

		$Filters['Sort'] = match($this->Data->Sort) {
			'oldest', 'newest', 'alias', 'email'
			=> $this->Data->Sort,
			default
			=> 'oldest'
		};

		////////

		$Users = User\EntitySession::Find($Filters);
		$AccessTypes = User\EntityAccessType::Find([ 'Index'=> TRUE ]);

		$Searched = (
			TRUE
			&& $this->Data->Exists('Q')
			&& $this->Data->Exists('AccessType')
			&& $this->Data->Exists('Sort')
		);

		($this->App->Surface)
		->Wrap('admin/users/list', [
			'Searched'    => $Searched,
			'Users'       => $Users,
			'AccessTypes' => $AccessTypes
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/ops/users/:UserID:')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleView(int $UserID):
	void {

		$Who = User\EntitySession::GetByID($UserID);
		$AccessTypes = $Who->GetAccessTypes();
		//$UsedAccessTypes = User\EntityAccessType::Find([ 'Index'=> TRUE ]);
		$DefinedAccessTypes = Atlantis\User\AccessTypeList::Fetch($this->App);

		($this->App->Surface)
		->Wrap('admin/users/view', [
			'Who'                => $Who,
			'AccessTypes'        => $AccessTypes,
			//'UsedAccessTypes'    => $UsedAccessTypes,
			'DefinedAccessTypes' => $DefinedAccessTypes
		]);

		return;
	}


}
