<?php

namespace Nether\Atlantis\Routes\User;
use Nether;
use League;

use Throwable;
use Nether\User\Library;
use Nether\Atlantis\PublicWeb;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Object\Datastore;
use Nether\Common\Datafilters;

use League\OAuth2\Client\Provider\Github as GitHubProvider;
use League\OAuth2\Client\Token\AccessToken;

class UserSessionWeb
extends PublicWeb {

	#[RouteHandler('/login')]
	public function
	PageLogin():
	void {

		($this->App->Surface)
		->Wrap('user/login');

		return;
	}

	#[RouteHandler('/logout')]
	public function
	PageLogout():
	void {

		($this->App->Surface)
		->Wrap('user/logout');

		return;
	}

	#[RouteHandler('/login/reset')]
	public function
	PageForgot():
	void {

		$Sent = $this->App->YoinkLocalData('LoginResetSent');
		$Code = $this->Query->Code;

		($this->App->Surface)
		->Wrap('user/reset', [
			'Sent'      => $Sent,
			'ResetCode' => $Code
		]);

		return;
	}

	#[RouteHandler('/signup')]
	public function
	PageJoin():
	void {

		($this->App->Surface)
		->Wrap('user/signup');

		return;
	}

	#[RouteHandler('/activate')]
	public function
	PageActivate():
	void {

		($this->App->Surface)
		->Wrap('user/activate');

		return;
	}

}
