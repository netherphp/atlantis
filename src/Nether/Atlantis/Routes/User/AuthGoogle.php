<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\User;

use Nether\Avenue\Meta\RouteHandler;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Google as GoogleProvider;

class AuthGoogle
extends OAuth2API {

	const
	AuthName  = 'Google',
	AuthField = 'AuthGoogleID';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[RouteHandler('/auth/google')]
	#[Atlantis\Meta\UserActivationFlow]
	public function
	HandleGoogle():
	void {

		parent::HandleAuthFlow();
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	IsAuthEnabled():
	bool {

		return User\Library::IsGoogleEnabled();
	}

	protected function
	GetAuthClient():
	AbstractProvider {

		$ClientID = User\Library::Get(User\Library::ConfGoogleID);
		$ClientSecret = User\Library::Get(User\Library::ConfGoogleSecret);

		$Client = new GoogleProvider([
			'clientId'     => $ClientID,
			'clientSecret' => $ClientSecret,
			'redirectUri'  => (new Atlantis\WebURL('/auth/google'))->Get()
		]);

		return $Client;
	}

	protected function
	GetAuthScopes():
	array {

		return [ 'openid' ];
	}

	protected function
	GetUserByAuthType(mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByGoogleID($AuthID);
	}

	protected function
	GetUserByAuthEmail(string $Email, mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByGoogleEmail($Email, $AuthID);
	}

}
