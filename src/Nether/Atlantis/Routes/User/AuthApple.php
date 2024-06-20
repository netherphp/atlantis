<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\User;

use Nether\Avenue\Meta\RouteHandler;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Apple as AppleProvider;

class AuthApple
extends OAuth2API {

	const
	AuthName  = 'Apple',
	AuthField = 'AuthAppleID';

	#[RouteHandler('/auth/apple', Verb: 'GET')]
	#[RouteHandler('/auth/apple', Verb: 'POST')]
	#[Atlantis\Meta\UserActivationFlow]
	public function
	HandleApple():
	void {

		parent::HandleAuthFlow();
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	IsAuthEnabled():
	bool {

		return User\Library::IsAppleEnabled();
	}

	protected function
	GetAuthClient():
	AbstractProvider {

		$ClientID = User\Library::Get(User\Library::ConfAppleID);
		$TeamID = User\Library::Get(User\Library::ConfAppleTeamID);
		$KeyFileID = User\Library::Get(User\Library::ConfAppleKeyFileID);
		$KeyFilePath = User\Library::Get(User\Library::ConfAppleKeyFilePath);

		$Client = new AppleProvider([
			'clientId'     => $ClientID,
			'teamId'       => $TeamID,
			'keyFileId'    => $KeyFileID,
			'keyFilePath'  => $KeyFilePath,
			'redirectUri'  => (new Atlantis\WebURL('/auth/apple'))->Get()
		]);

		return $Client;
	}

	protected function
	GetAuthScopes():
	array {

		return [ 'email' ];
	}

	protected function
	GetUserByAuthType(mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByAppleID($AuthID);
	}

	protected function
	GetUserByAuthEmail(string $Email, mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByAppleEmail($Email, $AuthID);
	}

}
