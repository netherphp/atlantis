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

	#[RouteHandler('/api/user/entity', Verb: 'GET')]
	#[RouteAccessTypeAdmin]
	public function
	EntityGet():
	void {

		$ID = Common\Filters\Numbers::IntType($this->Data->ID);

		if(!$ID)
		$this->Quit(1, 'no user id provided');

		////////

		$Entity = User\Entity::GetByID($ID);

		if(!$Entity)
		$this->Quit(2, 'no user entity found');

		$this->SetPayload([
			'User' => $Entity
		]);

		return;
	}

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

		////////

		if(count($Dataset)) {
			$User->Update($Dataset);

			$this->App->Log->Admin("USER-PATCH: {$User} by {$this->User}", [
				'Fields'  => array_keys($Dataset),
				'UserID'  => $User->ID,
				'AdminID' => $this->User->ID
			]);
		}

		$this->SetPayload($Dataset);
		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'DELETE')]
	#[RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		$this->SetMessage('DELETE');

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

		$this->App->Log->Admin("USER-BAN: {$User} by {$this->User}",[
			'UserID'  => $User->ID,
			'AdminID' => $this->User->ID
		]);

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

		$this->App->Log->Admin("USER-UNBAN: {$User} BY {$this->User}",[
			'UserID'  => $User->ID,
			'AdminID' => $this->User->ID
		]);

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

		$this->App->Log->Admin("USER-OVERSHADOW: {$Who} BY {$this->User}", [
			'UserID'  => $Who->ID,
			'AdminID' => $this->User->ID
		]);

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

	#[RouteHandler('/api/user/entity', Verb: 'SETACCESS')]
	#[RouteAccessTypeAdmin]
	public function
	EntitySetAccess():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->Key(Common\Filters\Text::TrimmedNullable(...))
		->Value(Common\Filters\Numbers::IntType(...))
		->Overwrite(Common\Filters\Numbers::BoolType(...));

		////////

		$User = User\Entity::GetByID($this->Data->ID);

		if(!$User)
		$this->Quit(1, 'User not found.');

		if(!$this->Data->Key)
		$this->Quit(2, 'No Key specified');

		////////

		$Access = $User->GetAccessTypes();
		$Key = NULL;
		$Type = NULL;
		$Updated = FALSE;

		// do a case-insense check if the key already exists.

		foreach($Access as $Key => $Type) {
			/** @var User\EntityAccessType $Type */

			if(strtolower($Key) === strtolower($this->Data->Key)) {
				if(!$this->Data->Overwrite)
				$this->Quit(3, "Key {$Key} already exists: {$Type->Value}");

				$Updated = TRUE;
				$Type->Update([
					'TimeCreated' => time(),
					'Value'       => $this->Data->Value
				]);
			}
		}

		// add the permission otherwise.

		if(!$Updated) {
			User\EntityAccessType::Insert([
				'EntityID' => $User->ID,
				'Key'      => $this->Data->Key,
				'Value'    => $this->Data->Value
			]);
		}

		$this->App->Log->Admin("USER-AXSET: {$User} by {$this->User}",[
			'Key'     => $this->Data->Key,
			'Value'   => $this->Data->Value,
			'UserID'  => $User->ID,
			'AdminID' => $this->User->ID
		]);

		////////

		$this
		->SetGoto('reload')
		->SetPayload([
			'Key'   => $this->Data->Key,
			'Value' => $this->Data->Value
		]);

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'DELACCESS')]
	#[RouteAccessTypeAdmin]
	public function
	EntityDeleteAccess():
	void {

		($this->Data)
		->AccessID(Common\Filters\Numbers::IntType(...));

		////////

		$Access = User\EntityAccessType::GetByID($this->Data->AccessID);

		if(!$Access)
		$this->Quit('1', 'Invalid AccessID');

		$User = User\Entity::GetByID($Access->EntityID);
		$Access->Drop();

		$this->App->Log->Admin("USER-AXDEL: {$User} by {$this->User}",[
			'Key'     => $Access->Key,
			'Value'   => $Access->Value,
			'UserID'  => $User->ID,
			'AdminID' => $this->User->ID
		]);

		$this->SetGoto('reload');
		return;
	}

}