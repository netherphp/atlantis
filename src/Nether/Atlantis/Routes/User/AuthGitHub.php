<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\User;

use Throwable;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Common\Datastore;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Github as GitHubProvider;

class AuthGitHub
extends OAuth2API {

	const
	AuthName  = 'GitHub',
	AuthField = 'AuthGitHubID';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[RouteHandler('/auth/github')]
	#[Atlantis\Meta\UserActivationFlow]
	public function
	HandleGitHub():
	void {

		parent::HandleAuthFlow();
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	IsAuthEnabled():
	bool {

		return User\Library::IsGitHubEnabled();
	}

	protected function
	GetAuthClient():
	AbstractProvider {

		$ClientID = User\Library::Get(User\Library::ConfGitHubID);
		$ClientSecret = User\Library::Get(User\Library::ConfGitHubSecret);

		$Client = new GitHubProvider([
			'clientId'     => $ClientID,
			'clientSecret' => $ClientSecret
		]);

		return $Client;
	}

	protected function
	GetAuthScopes():
	array {

		return [ 'user:email' ];
	}

	protected function
	GetUserByAuthType(mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByGitHubID($AuthID);
	}

	protected function
	GetUserByAuthEmail(string $Email, mixed $AuthID):
	?User\EntitySession {

		return User\EntitySession::GetByGitHubEmail($Email, $AuthID);
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
			/** @var League\OAuth2\Client\Provider\GithubResourceOwner $Account */

			$Account = $Client->GetResourceOwner($Token);
			$AuthID = $Account->GetID();
			$Alias = Avenue\Util::MakePathableKey($Account->GetNickname());
			$Email = Common\Filters\Text::Email($Account->GetEmail());

			// github tends to not return an email address even if
			// you have one set as public, so, here we go.

			if(!$Email) {
				$Request = $Client->GetAuthenticatedRequest(
					'GET', 'https://api.github.com/user/emails',
					$Token
				);

				$Emails = (
					(new Datastore((array)$Client->GetParsedResponse($Request)))
					->Filter(function($Val){ return $Val['primary'] === TRUE; })
					->Remap(function($Val){ return $Val['email']; })
				);

				if($Emails->Count() >= 1)
				$Email = $Emails->Shift();
			}
		}

		catch(Throwable $Error) { }

		return (object)[
			'Alias'  => $Alias,
			'Email'  => $Email,
			'AuthID' => $AuthID
		];
	}

}
