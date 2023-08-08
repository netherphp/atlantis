<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

use Throwable;
use Nether\Avenue\Meta\RouteHandler;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Wohali\OAuth2\Client\Provider\Discord as DiscordProvider;

class AuthDiscord
extends OAuth2API {

	const
	AuthName  = 'Discord',
	AuthField = 'AuthDiscordID';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[RouteHandler('/auth/discord')]
	#[Atlantis\Meta\UserActivationFlow]
	public function
	HandleAuthFlow():
	void {

		parent::HandleAuthFlow();
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	IsAuthEnabled():
	bool {

		return User\Library::IsDiscordEnabled();
	}

	protected function
	GetAuthClient():
	AbstractProvider {

		$ClientID = User\Library::Get(User\Library::ConfDiscordID);
		$ClientSecret = User\Library::Get(User\Library::ConfDiscordSecret);

		$Client = new DiscordProvider([
			'clientId'     => $ClientID,
			'clientSecret' => $ClientSecret,
			'redirectUri'  => (new Atlantis\WebURL('/auth/discord'))->Get()
		]);

		return $Client;
	}

	protected function
	GetAuthScopes():
	array {

		return [ 'identify', 'email' ];
	}

	protected function
	GetUserByAuthType(mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByDiscordID($AuthID);
	}

	protected function
	GetUserByAuthEmail(string $Email, mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByDiscordEmail($Email, $AuthID);
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
			/** @var \Wohali\OAuth2\Client\Provider\DiscordResourceOwner $Account */
			$Account = $Client->GetResourceOwner($Token);
			$AuthID = $Account->GetID();
			$Alias = $Account->GetUsername();
			$Email = Common\Filters\Text::Email($Account->GetEmail());
		}

		catch(Throwable $Error) { throw $Error; }

		return (object)[
			'Alias'  => $Alias,
			'Email'  => $Email,
			'AuthID' => $AuthID
		];
	}


}
