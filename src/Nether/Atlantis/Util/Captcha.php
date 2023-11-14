<?php

namespace Nether\Atlantis\Util;

use Nether\Atlantis;

class Captcha {

	protected CaptchaProviderInterface
	$API;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Atlantis\Engine $App) {

		if($App->Config->HasKey('Google.ReCaptcha.PublicKey'))
		$this->API = new Captcha\GoogleCaptcha($App);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsConfigured():
	bool {

		return isset($this->API);
	}

	public function
	IsValid():
	bool {

		if(!isset($this->API))
		return TRUE;

		////////

		return $this->API->IsValid();
	}

	public function
	GetHTML():
	string {

		if(!isset($this->API))
		return '';

		////////

		return $this->API->GetHTML();
	}

}
