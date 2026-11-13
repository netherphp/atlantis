<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Captcha;

use Nether\Atlantis;
use Nether\Captcha;
use Nether\Datastore;
use Nether\Filter;

################################################################################
################################################################################

class API
extends Atlantis\PublicAPI {

	#[Atlantis\Meta\RouteHandler('/api/captcha', Verb: 'CHECK')]
	public function
	HandleCheck():
	void {

		// error codes
		// 1 - unknown captcha type
		// 2 - rate limit

		if(!$this->HandleRateLimiting('CHECK:/api/captcha'))
		$this->Quit(2, 'Slow Down');

		////////

		($this->Inputs)
		->SetValueFilter('Type', Filter\Text::TrimmedNullable(...));

		////////

		match($this->Inputs->Get('Type')) {
			'nether' => $this->HandleCheckNether(),
			default  => $this->HandleCheckUnknown()
		};

		////////

		return;
	}

	////////////////////////////////////////////////////////////////

	protected function
	HandleCheckNether():
	void {

		$Valid = Captcha::ValidateFromInput(
			$this->Inputs->Export()
		);

		$this->SetPayload([
			'Type'  => 'nether',
			'Valid' => $Valid
		]);

		////////

		return;
	}

	protected function
	HandleCheckUnknown():
	void {

		$this->SetPayload([
			'Type'  => 'unknown',
			'Valid' => false
		]);

		$this->Quit(1, 'unknown captcha type');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/captcha', Verb: 'FRESH')]
	public function
	HandleFresh():
	void {

		// error codes
		// 1 - unknown captcha type
		// 2 - rate limit

		if(!$this->HandleRateLimiting('FRESH:/api/captcha', 20, 4))
		$this->Quit(2, 'Slow Down');

		////////

		($this->Inputs)
		->SetValueFilter('Type', Filter\Text::TrimmedNullable(...));

		////////

		match($this->Inputs->Get('Type')) {
			'nether' => $this->HandleFreshNether(),
			default  => $this->HandleFreshUnknown()
		};

		////////

		return;
	}

	protected function
	HandleFreshNether():
	void {

		$Captcha = Captcha::Fresh();

		$this->SetPayload([
			'Type'  => 'nether',			
			'Token' => $Captcha->GetTokenForForm(),
			'Image' => $Captcha->GetImageEncoded()
		]);

		////////

		return;
	}

	protected function
	HandleFreshUnknown():
	void {

		$this->SetPayload([
			'Type'  => 'unknown',
			'Valid' => false
		]);

		$this->Quit(1, 'unknown captcha type');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	HandleRateLimiting(string $Endpoint, int $TimeWindow=20, int $MaxAttempts=10):
	bool {

		$RateLimit = Atlantis\Systems\RateLimiter\Tool::New(
			App: $this->App,
			UserKey: $this->Request->GetTrafficHash(),
			Endpoint: $Endpoint,
			TimeWindow: $TimeWindow,
			MaxAttempts: $MaxAttempts
		);

		////////

		if($RateLimit->HasHitLimit()) {
			($this->Response)
			->SetCode($this->Response::CodeSlowDown);

			return FALSE;
		}

		////////

		$RateLimit->Bump();

		return TRUE;
	}

};
