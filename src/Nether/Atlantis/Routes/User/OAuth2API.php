<?php

namespace Nether\Atlantis\Routes\User;
use Nether;

use Throwable;
use Nether\Atlantis\PublicWeb;
use Nether\Common\Datafilters;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

abstract class OAuth2API
extends PublicWeb {

	const
	AuthName = NULL,
	AuthField = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HandleAuthFlow():
	void {

		($this->Request->Data)
		->Code(Datafilters::TrimmedTextNullable(...))
		->Goto(Datafilters::Base64Decode(...));

		$AllowNewUsers = TRUE;
		$Goto = $this->Request->Data->Goto;
		$AuthCode = $this->Request->Data->Code;
		$Token = NULL;
		$User = NULL;
		$RemoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL;

		////////

		if(!$this->IsAuthEnabled())
		$this->Quit(1, sprintf('%s authentication is not enabled', static::AuthName));

		////////

		// kick off an auth flow with redirect to the remote app. this will
		// end the current request, sending them elsewhere. when the
		// remote is done they will be sent back with an auth code in tow.

		$Client = $this->GetAuthClient();

		if(!$AuthCode) {
			if($Goto)
			$_SESSION['Nether.Atlantis.Login.Goto'] = $Goto;

			$this->Goto($Client->GetAuthorizationUrl([
				'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
				'scope' => $this->GetAuthScopes()
			]));
		}

		////////

		// check our local database to see if we have matching local user
		// already in the database using the github info.

		try {

			// finish an in-process auth flow by using the auth code returned
			// to get a full auth token.

			$Token = $this->FetchAuthToken($Client, $AuthCode);

			if(!$Token)
			$this->Quit(2, sprintf('Unable to process %s Auth Code', static::AuthName));

			// at this point we should have basic access to the 	user info
			// on the remote host so find what we want to fill in an account
			// here locally.

			$Info = $this->FetchRemoteUserInfo($Client, $Token);

			if(!$Info->Email)
			$this->Quit(3, sprintf('%s did not give us an Email', static::AuthName));

			if(!$Info->AuthID)
			$this->Quit(9, sprintf('%s did not give us an AuthID', static::AuthName));

		}

		catch(Throwable $Error) {
			$this->Quit(8, "Unexpected auth error occured ({$Error->GetMessage()}).");
		}

		////////

		try {

			// first actually lets see if a current user wants to connect.

			if($this->User) {
				$User = $this->GetUserByAuthType($Info->AuthID);

				if($User && $User->ID !== $this->User->ID)
				$this->Quit(7, sprintf(
					'This %s identity is already bound to another account.',
					static::AuthName
				));

				$User = $this->User;
			}

			// if there is no user then check for an account using the github
			// auth id.

			if(!$User)
			$User = $this->GetUserByAuthType($Info->AuthID);

			// if we have not found a user yet check for an account with the
			// same email address.

			// @todo 2022-12-13
			// consider not allowing gank-by-email. linking while logged in
			// is now working and i think this is a less good idea than it
			// sounded like while bootstrapping the project. maybe an option
			// default disabled. it really comes down to do you trust that
			// the third party auth handled the checking user owning that
			// email. and even then, this could still cause a condition where
			// a domain changes hands so a new person accesses a colliding
			// account of an older previous person. i can see arguments both
			// ways which is why i am leaning towards a setting that defaults
			// disabled.

			if(!$User)
			$User = $this->GetUserByAuthEmail($Info->Email, $Info->AuthID);

			// is there already a user with this alias though? if there is just
			// force it to null for now and the onboarding flow later will
			// crowbar one out of them if demanded.

			if($Info->Alias) {
				$AConflict = Nether\User\Entity::GetByAlias($Info->Alias);

				if($AConflict)
				$Info->Alias = NULL;

				unset($AConflict);
			}

			// if we have not found a user yet and we allow new users to be
			// created on the fly then go ahead and insert them now.

			if(!$User && $AllowNewUsers) {
				$User = Nether\User\EntitySession::Insert([
					static::AuthField => $Info->AuthID,
					'Alias'           => $Info->Alias,
					'Email'           => $Info->Email,
					'Activated'       => 1,
					'RemoteAddr'      => $RemoteAddr
				]);

				$this->App->Log->Main(
					"USER-CREATE: {$User}",
					[ 'UserID'=> $User->ID, 'Origin'=> static::AuthName ]
				);
			}
		}

		catch(Nether\User\Error\AuthMismatch $Error) {
			$this->Quit(4, sprintf(
				'This account is already bound to a different %s identity.',
				static::AuthName
			));
		}

		catch(Throwable $Error) {
			$this->Quit(5, "Unexpected error occured ({$Error->GetMessage()}).");
		}

		////////

		if(!$User)
		$this->Quit(6, sprintf(
			'There are no accounts linked with this %s identity.',
			static::AuthName
		));

		//if($User->TimeBanned !== 0)
		//$this->Quit(7, 'This account is banned.');

		////////

		if(!$User->{static::AuthField})
		$this->App->Log->Main(
			"USER-AUTHLINK: {$User}",
			[ 'UserID'=> $User->ID, 'Origin'=> static::AuthName ]
		);

		$User->Update([
			static::AuthField => $Info->AuthID,
			'TimeSeen'        => time()
		]);

		$User->TransmitSession();

		////////

		$Goto = '/';

		if(isset($_SESSION['Nether.Atlantis.Login.Goto'])) {
			$Goto = $_SESSION['Nether.Atlantis.Login.Goto'];
			unset($_SESSION['Nether.Atlantis.Login.Goto']);
		}

		$this->Goto($Goto);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	abstract protected function
	GetAuthClient():
	AbstractProvider;

	abstract protected function
	GetAuthScopes():
	array;

	abstract protected function
	IsAuthEnabled():
	bool;

	abstract protected function
	GetUserByAuthType(mixed $AuthID):
	?Nether\User\EntitySession;

	abstract protected function
	GetUserByAuthEmail(string $Email, mixed $AuthID):
	?Nether\User\EntitySession;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	FetchAuthToken(AbstractProvider $Client, string $AuthCode):
	?AccessToken {

		$Token = NULL;

		try {
			$Token = $Client->GetAccessToken(
				'authorization_code',
				[ 'code' => $AuthCode ]
			);
		}

		catch(Throwable $Error) { }

		return $Token;
	}

	protected function
	FetchRemoteUserInfo(AbstractProvider $Client, AccessToken $Token):
	object {

		$Alias = NULL;
		$Email = NULL;
		$AuthID = NULL;
		$Error = NULL;

		////////

		try {
			/** @var League\OAuth2\Client\Provider\GoogleUser $Account */

			$Account = $Client->GetResourceOwner($Token);
			$AuthID = $Account->GetID();
			$Alias = NULL;
			$Email = NULL;

			if(method_exists($Account, 'GetEmail'))
			$Email = Nether\Common\Datafilters::Email($Account->GetEmail());
		}

		catch(Throwable $Error) { }

		return (object)[
			'Alias'  => $Alias,
			'Email'  => $Email,
			'AuthID' => $AuthID
		];
	}

}