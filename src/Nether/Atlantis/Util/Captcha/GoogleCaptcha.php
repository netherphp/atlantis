<?php

namespace Nether\Atlantis\Util\Captcha;

use Nether\Atlantis;
use ReCaptcha;

class GoogleCaptcha
implements Atlantis\Util\CaptchaProviderInterface {

	const
	ConfPublicKey = 'Google.ReCaptcha.PublicKey',
	ConfPrivateKey = 'Google.ReCaptcha.PrivateKey';

	protected ?string
	$PublicKey = NULL;

	protected ?string
	$PrivateKey = NULL;

	protected ?string
	$Theme = NULL;

	public function
	__Construct(Atlantis\Engine $App) {

		$this->PublicKey = $App->Config->Get(static::ConfPublicKey);
		$this->PrivateKey = $App->Config->Get(static::ConfPrivateKey);
		$this->Theme = $App->Surface->Get('Theme.Page.ThemeMode');

		($App->Surface)
		->AddScriptURL('https://www.google.com/recaptcha/api.js');

		return;
	}

	public function
	IsValid():
	bool {

		$Cap = new ReCaptcha\ReCaptcha($this->PrivateKey);

		$Verify = $Cap->Verify(
			$_POST['g-recaptcha-response'],
			$_SERVER['REMOTE_ADDR']
		);

		return $Verify->IsSuccess();
	}

	public function
	GetHTML():
	string {

		return sprintf(
			'<div class="g-recaptcha" data-sitekey="%s" data-theme="%s"></div>',
			$this->PublicKey,
			$this->Theme ?: 'dark'
		);
	}

}
