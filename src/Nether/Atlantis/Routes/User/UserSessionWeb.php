<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

use Nether\Atlantis\PublicWeb;
use Nether\Avenue\Meta\RouteHandler;

class UserSessionWeb
extends PublicWeb {

	public function
	OnReady(?Common\Datastore $Data):
	void {

		parent::OnReady($Data);

		if(!User\Entity::HasDB())
		$this->ErrorDevQuit(
			'Missing Database Config',
			sprintf('No database for %s found', User\Entity::$DBA)
		);


		return;
	}

	#[RouteHandler('/login')]
	#[Atlantis\Meta\UserActivationFlow]
	public function
	PageLogin():
	void {

		($this->App->Surface)
		->Wrap('user/login');

		return;
	}

	#[RouteHandler('/logout')]
	#[Atlantis\Meta\UserActivationFlow]
	public function
	PageLogout():
	void {

		($this->App->Surface)
		->Wrap('user/logout');

		return;
	}

	#[RouteHandler('/login/reset')]
	#[Atlantis\Meta\UserActivationFlow]
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
	#[Atlantis\Meta\UserActivationFlow]
	public function
	PageSignup():
	void {

		($this->App->Surface)
		->Wrap('user/signup',[
			'RequireAlias' => $this->Config[Atlantis\Key::ConfUserRequireAlias]
		]);

		return;
	}

	#[RouteHandler('/login/activate')]
	#[Atlantis\Meta\UserActivationFlow]
	public function
	PageActivate():
	void {

		($this->App->Surface)
		->Wrap('user/activate',[
			'RequireAlias' => $this->Config[Atlantis\Key::ConfUserRequireAlias],
			'RequireEmail' => $this->Config[Atlantis\Key::ConfUserEmailActivate],
			'Activated'    => $this->User->Activated
		]);

		return;
	}

}
