<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

use Nether\Avenue\Meta\RouteHandler;
use Nether\Atlantis\Meta\RouteAccessTypeAdmin;

class UserEntityAPI
extends Atlantis\ProtectedAPI {


	#[RouteHandler('/api/user/entity', Verb: 'GET')]
	#[RouteAccessTypeAdmin]
	public function
	EntityGet():
	void {

		$this->SetMessage('GET');

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

		$this->SetMessage('PATCH');

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
		->ID(Common\Datafilters::TypeInt(...))
		->Reason(Common\Datafilters::TrimmedTextNullable(...));

		////////

		$User = User\Entity::GetByID($this->Data->ID);

		if(!$User)
		$this->Quit(1, 'User not found.');

		if($User->ID === $this->User->ID)
		$this->Quit(2, 'Really?');

		////////

		$User->Update([ 'TimeBanned'=> time() ]);

		$this->SetGoto('reload');
		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'UNBAN')]
	#[RouteAccessTypeAdmin]
	public function
	EntityUnban():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...));

		////////

		$User = User\Entity::GetByID($this->Data->ID);

		if(!$User)
		$this->Quit(1, 'User not found.');

		////////

		$User->Update([ 'TimeBanned'=> 0 ]);

		$this->SetGoto('reload');
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
		->ID(Common\Datafilters::TypeInt(...))
		->Key(Common\Datafilters::TrimmedTextNullable(...))
		->Value(Common\Datafilters::TrimmedTextNullable(...))
		->Overwrite(Common\Datafilters::TypeBool(...));

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
		->AccessID(Common\Datafilters::TypeInt(...));

		////////

		$Access = User\EntityAccessType::GetByID($this->Data->AccessID);

		if(!$Access)
		$this->Quit('1', 'Invalid AccessID');

		$Access->Drop();

		$this->SetGoto('reload');
		return;
	}

}