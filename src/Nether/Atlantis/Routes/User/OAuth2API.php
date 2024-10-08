<?php

namespace Nether\Atlantis\Routes\User;
use Nether;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

use Throwable;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

abstract class OAuth2API
extends Atlantis\PublicWeb {

	const
	AuthName = NULL,
	AuthField = NULL;

	static public function
	GetAuthKey():
	string {

		return Avenue\Util::MakePathableKey(static::AuthName ?? '');
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HandleAuthFlow():
	void {

		($this->Request->Data)
		->Code(Common\Filters\Text::TrimmedNullable(...))
		->Goto(Common\Filters\Text::Base64Decode(...));

		$AllowSignup = $this->App->Config[Atlantis\Key::ConfUserAllowSignup];
		$AllowSignupGank = $this->App->Config[Atlantis\Key::ConfUserAllowSignupGank];
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

			return;
		}

		////////

		// check our local database to see if we have matching local user
		// already in the database using the github info.

		try {

			// finish an in-process auth flow by using the auth code returned
			// to get a full auth token.

			$Token = $this->FetchAuthToken($Client, $AuthCode);

			//var_dump($Token);

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

			if(!$User) {
				$User = $this->GetUserByAuthEmail($Info->Email, $Info->AuthID);

				if($User && !$User->{static::AuthField})
				if(!$AllowSignupGank)
				$this->Quit(10, sprintf(
					'An account with the Email supplied by %s (%s) already exists.',
					static::AuthName,
					$Info->Email
				));
			}

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

			if(!$User && $AllowSignup) {
				$User = Nether\User\EntitySession::Insert([
					static::AuthField => $Info->AuthID,
					'Alias'           => $Info->Alias,
					'Email'           => $Info->Email,
					'Activated'       => 1,
					'RemoteAddr'      => $RemoteAddr
				]);

				// log user create
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

		//if(!$User->{static::AuthField})
		// log access link

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

		catch(Throwable $Error) {
			error_log("[OAuth2API.FetchAuthToken] {$Error->GetMessage()}");
			error_log($Error->GetTraceAsString());
			error_log(get_class($Error));
			error_log(json_encode($Error));
		}

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
			$Email = Common\Filters\Text::Email($Account->GetEmail());
		}

		catch(Throwable $Error) { }

		return (object)[
			'Alias'  => $Alias,
			'Email'  => $Email,
			'AuthID' => $AuthID
		];
	}

}