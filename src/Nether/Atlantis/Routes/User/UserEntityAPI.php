<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Database;
use Nether\Common;
use Nether\User;

use Nether\Avenue\Meta\RouteHandler;
use Nether\Atlantis\Meta\RouteAccessTypeAdmin;
use Nether\Common\Prototype\PropertyInfo;

class UserEntityAPI
extends Atlantis\ProtectedAPI {

	public function
	OnReady(?Common\Datastore $Input):
	void {

		parent::OnReady($Input);

		////////

		($this->Data)
		->FilterPush('ID', Common\Filters\Numbers::IntNullable(...))
		->FilterPush('UUID', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Page', Common\Filters\Numbers::Page(...));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// 2026-05 audit

	#[RouteHandler('/api/user/entity', Verb: 'GET')]
	#[RouteAccessTypeAdmin]
	public function
	EntityGet():
	void {

		$Entity = $this->DemandEntityByID($this->Data->Get('ID'));
		$Access = $Entity->GetAccessTypes();

		////////

		$this->SetPayload([
			'User'   => $Entity->DescribeForPublicAPI(),
			'Access' => $Access->Map(
				fn(User\EntityAccessType $T)
				=> $T->DescribeForPublicAPI()
			)
		]);

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'GRANT')]
	#[RouteAccessTypeAdmin]
	public function
	EntityAccessGrant():
	void {

		$Entity = $this->DemandEntityByID($this->Data->Get('ID'));
		$Access = $Entity->GetAccessTypes();

		$Key = Common\Filters\Text::Trimmed($this->Data->Get('Key'));
		$Val = Common\Filters\Numbers::IntType($this->Data->Get('Value'));
		$New = NULL;

		////////

		$Access->Filter(fn(User\EntityAccessType $A)
			=> strtolower($Key) === strtolower($A->Key)
		);

		// update existing entry.

		if($Access->Count()) {
			$New = $Access->Current();
			$New->Update([
				'TimeCreated' => Common\Date::Unixtime(),
				'Value'       => $Val
			]);
		}

		// insert new entry.

		else {
			$New = User\EntityAccessType::Insert([
				'EntityID' => $Entity->ID,
				'Key'      => $Key,
				'Value'    => $Val
			]);
		}

		////////

		($this)
		->SetGoto('reload')
		->SetPayload($New);

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'REVOKE')]
	#[RouteAccessTypeAdmin]
	public function
	EntityAccessRevoke():
	void {

		$AID = Common\Filters\Numbers::IntType($this->Data->Get('AccessID'));
		$Access = User\EntityAccessType::GetByID($AID);

		if(!$Access)
		$this->Quit('1', 'Invalid AccessID');

		////////

		$Access->Drop();

		////////

		($this)
		->SetGoto('reload')
		->SetPayload([
			'AccessID' => $Access->ID,
			'UserID'   => $Access->EntityID,
			'Key'      => $Access->Key,
			'Value'    => $Access->Value
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[RouteHandler('/api/user/entity', Verb: 'POST')]
	#[RouteAccessTypeAdmin]
	public function
	EntityPost():
	void {

		$this->SetMessage('POST');

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'PATCH')]
	#[RouteAccessTypeAdmin]
	public function
	EntityPatch():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$User = User\Entity::GetByID($this->Data->ID);

		if(!$User)
		$this->Quit(1, 'User not found.');

		////////

		// fetch a dataset of all the things valid to change that have changed.

		$Patchables = Common\Meta\PropertyPatchable::FromClass(User\Entity::class);
		$Prop = NULL;
		$Filters = NULL;
		$Filter = NULL;
		$Dataset = [];

		foreach($Patchables as $Prop => $Filters) {
			if(!$this->Data->Exists($Prop))
			continue;

			$Dataset[$Prop] = $this->Data->Get($Prop);

			foreach($Filters as $Filter)
			$Dataset[$Prop] = $Filter($Dataset[$Prop]);
		}

		////////

		// some things cannot be changed without a check.

		if(array_key_exists('Alias', $Dataset)) {
			$Existing = User\Entity::GetByAlias($Dataset['Alias']);

			if($Existing && $Existing->ID !== $User->ID)
			$this->Quit(2, 'Alias already in use.');
		}

		if(array_key_exists('Email', $Dataset)) {
			$Existing = User\Entity::GetByEmail($Dataset['Email']);

			if($Existing && $Existing->ID !== $User->ID)
			$this->Quit(2, 'Email already in use.');
		}

		if(array_key_exists('Admin', $Dataset)) {
			if($User->ID === $this->User->ID)
			if(!$Dataset['Admin']) {
				$this->Quit(3, 'You do not want to de-admin yourself.');
			}
		}

		////////

		if(count($Dataset)) {
			$User->Update($Dataset);
			// log patch
		}

		$this->SetPayload($Dataset);
		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'DELETE')]
	#[RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		$ID = Common\Filters\Numbers::IntNullable($this->Data->Get('ID'));

		if(!$ID)
		$this->Quit(1, 'no ID specified');

		////////

		$Who = User\Entity::GetByID($ID);

		if(!$Who)
		$this->Quit(2, 'user not found');

		////////

		if($Who->ID === $this->User->ID)
		$this->Quit(3, 'DURRR DONT DELETE YOURSELF IDIOT');

		////////

		// before implementing the delete also implement the
		// on delete plugin api.

		$this->SetMessage('DELETE');

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'SEARCH')]
	#[RouteAccessTypeAdmin]
	public function
	EntitySearch():
	void {

		($this->Data)
		->FilterPush('Alias', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Email', Common\Filters\Text::TrimmedNullable(...));

		////////

		$QueryField = match(TRUE) {
			($this->Data->Exists('Email')) => 'Email',
			default                        => 'Alias'
		};

		$QueryValue = $this->Data->Get($QueryField);

		$Limit = 50;
		$Page = 1;

		$Result = User\Entity::Find([
			'Search'      => $QueryValue,
			'SearchAlias' => ($QueryField === 'Alias'),
			'SearchEmail' => ($QueryField === 'Email'),
			'Limit'       => $Limit,
			'Page'        => $Page
		]);

		$this->SetPayload([
			'QueryField' => $QueryField,
			'QueryValue' => $QueryValue,
			'Limit'      => $Limit,
			'Page'       => $Page,
			'Results'    => $Result->Export()
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[RouteHandler('/api/user/entity', Verb: 'BAN')]
	#[RouteAccessTypeAdmin]
	public function
	EntityBan():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->Reason(Common\Filters\Text::TrimmedNullable(...));

		////////

		$User = User\Entity::GetByID($this->Data->ID);

		if(!$User)
		$this->Quit(1, 'User not found.');

		if($User->ID === $this->User->ID)
		$this->Quit(2, 'Really?');

		////////

		$User->Update([ 'TimeBanned'=> time() ]);

		// log ban

		$this->SetGoto('reload');
		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'UNBAN')]
	#[RouteAccessTypeAdmin]
	public function
	EntityUnban():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$User = User\Entity::GetByID($this->Data->ID);

		if(!$User)
		$this->Quit(1, 'User not found.');

		////////

		$User->Update([ 'TimeBanned'=> 0 ]);

		// log unban

		$this->SetGoto('reload');
		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'OVERSHADOW')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleOvershadow():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		////////

		$Who = User\EntitySession::GetByID($this->Data->ID);

		if(!$Who)
		$this->Quit(1, 'user not found');

		////////

		$Who->TransmitSession(TRUE);

		// log overshadow

		$this
		->SetGoto('/')
		->SetMessage('Assuming Direct Control')
		->SetPayload([
			'UserID' => $Who->ID
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////



	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	DemandEntityByID(?int $ID):
	User\Entity {

		if(!$ID)
		$this->Quit(1, 'no id specified');

		////////

		$Ent = User\Entity::GetByID($ID);

		if(!$Ent)
		$this->Quit(2, 'user not found');

		////////

		return $Ent;
	}

}