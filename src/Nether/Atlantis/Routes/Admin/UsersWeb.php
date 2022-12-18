<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

class UsersWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/users/list')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleList():
	void {

		($this->Data)
		->Q(Common\Datafilters::TrimmedTextNullable(...))
		->AccessType(Common\Datafilters::TypeIntNullable(...))
		->Sort(Common\Datafilters::TrimmedTextNullable(...));

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

		$Users = User\Entity::Find($Filters);
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
	public function
	HandleView(int $UserID):
	void {

		$Who = User\Entity::GetByID($UserID);
		$AccessTypes = $Who->GetAccessTypes();
		$DefinedAccessTypes = User\EntityAccessType::Find([ 'Index'=> TRUE ]);

		($this->App->Surface)
		->Wrap('admin/users/view', [
			'Who'                => $Who,
			'AccessTypes'        => $AccessTypes,
			'DefinedAccessTypes' => $DefinedAccessTypes
		]);

		return;
	}


}
