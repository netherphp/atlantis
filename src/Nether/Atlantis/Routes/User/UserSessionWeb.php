<?php

namespace Nether\Atlantis\Routes\User;

use Nether\Atlantis;
use Nether\Common;
use Nether\Email;
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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageLogin():
	void {

		($this->App->Surface)
		->Wrap('user/login');

		return;
	}

	#[RouteHandler('/logout')]
	#[Atlantis\Meta\UserActivationFlow]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageLogout():
	void {

		($this->App->Surface)
		->Wrap('user/logout');

		return;
	}

	#[RouteHandler('/login/reset')]
	#[Atlantis\Meta\UserActivationFlow]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageForgot():
	void {

		if(!$this->App->Config->Get(Email\Library::ConfOutboundVia))
		$this->ErrorDevQuit(
			'No Email Config',
			'This app requires Outbound email to be configured.'
		);

		//////

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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageSignup():
	void {

		$AllowSignup = $this->App->Config->Get(Atlantis\Key::ConfUserAllowSignup);
		$RequireAlias = $this->Config[Atlantis\Key::ConfUserRequireAlias];

		($this->App->Surface)
		->Wrap('user/signup',[
			'AllowSignup'  => $AllowSignup,
			'RequireAlias' => $RequireAlias
		]);

		return;
	}

	#[RouteHandler('/login/activate')]
	#[Atlantis\Meta\UserActivationFlow]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	PageActivate():
	void {

		if($this->User && $this->User->Activated)
		$this->Goto('/');

		($this->App->Surface)
		->Wrap('user/activate',[
			'RequireAlias' => $this->Config[Atlantis\Key::ConfUserRequireAlias],
			'RequireEmail' => $this->Config[Atlantis\Key::ConfUserEmailActivate],
			'Activated'    => $this->User->Activated
		]);

		return;
	}

}
