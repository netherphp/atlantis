<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\RateLimiter;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Atlantis\Systems\RateLimiter;

################################################################################
################################################################################

class Tool {

	const
	ConfStorageAPI = 'Nether.Atlantis.Systems.RateLimiter.StorageAPI';

	////////

	protected string
	$UKey;

	protected string
	$EKey;

	protected int
	$TimeWindow;

	protected int
	$MaxAttempts;

	////////

	protected Atlantis\Engine
	$App;

	protected RateLimiter\StorageAPI\ClientInterface
	$Client;

	protected Avenue\Struct\TrafficHash
	$HitHash;

	protected ?RateLimiter\StorageAPI\EntryInterface
	$HitData;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Atlantis\Engine $App, string $UserKey, string $Endpoint, int $TimeWindow, int $MaxAttempts) {

		$this->SetApp($App);
		$this->SetUserKey($UserKey);
		$this->SetEndpointKey($Endpoint);
		$this->SetTimeWindow($TimeWindow);
		$this->SetMaxAttempts($MaxAttempts);

		$this->PopulateClient();
		$this->PopulateHitHash();
		$this->PopulateHitData();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasHitLimit():
	bool {

		if(!isset($this->HitData))
		$this->PopulateHitData();

		////////

		if($this->HitData->GetAttemptsRemaining() > 0)
		return FALSE;

		////////

		return TRUE;
	}

	public function
	HasHitLimitIfNotBump():
	bool {

		$Hit = $this->HasHitLimit();

		////////

		if(!$Hit)
		$this->Bump();

		////////

		return $Hit;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Bump(int $Inc=-1):
	static {

		if(!isset($this->HitData))
		$this->PopulateHitData();

		////////

		$this->BumpHitData($Inc);

		////////

		return $this;
	}

	public function
	Delete():
	static {

		if(!isset($this->HitData))
		return $this;

		////////

		$this->Client->Delete($this->HitData);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	PopulateClient():
	static {

		$Class = $this->App->Config->Get(static::ConfStorageAPI);

		if(!$Class)
		throw new Common\Error\ConfigNotFound('Class', 'StorageAPI Class');

		////////

		$this->Client = new StorageAPI\Database\Client($this->App);

		return $this;
	}

	public function
	PopulateHitHash():
	static {

		if(isset($this->HitHash))
		unset($this->HitHash);

		////////

		$this->GenerateHitHash();

		return $this;
	}

	public function
	GenerateHitHash():
	static {

		if(!isset($this->UKey) || !$this->UKey)
		throw new Common\Error\RequiredDataMissing('ClientKey', 'string');

		if(!isset($this->EKey) || !$this->EKey)
		throw new Common\Error\RequiredDataMissing('EndpointKey', 'string');

		////////

		if(isset($this->HitHash))
		unset($this->HitHash);

		$this->HitHash = new Avenue\Struct\TrafficHash(
			$this->UKey,
			$this->EKey,
			'RateLimit::Tool<1>'
		);

		////////

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	PopulateHitData():
	static {

		if(!isset($this->HitHash))
		$this->GenerateHitHash();

		////////

		if(isset($this->HitData))
		unset($this->HitData);

		$this->HitData = $this->Client->Fetch($this->HitHash->Get());

		// if no data was found generate some.

		if($this->HitData === NULL)
		$this->GenerateHitData();

		// if the data was expired generate new.

		if($this->IsHitDataExpired())
		$this->GenerateHitData();

		////////

		return $this;
	}

	public function
	GenerateHitData():
	static {

		if(isset($this->HitData))
		unset($this->HitData);

		////////

		$this->HitData = $this->Client->Touch(
			$this->HitHash->Get(),
			$this->MaxAttempts
		);

		////////

		return $this;
	}

	public function
	BumpHitData(int $Inc):
	static {

		$this->Client->Bump($this->HitData, $Inc);

		return $this;
	}

	public function
	IsHitDataExpired():
	bool {

		$TimeLogged = $this->HitData->GetTimeLogged();
		$DeadTime = $this->GetDeadTime();

		return ($TimeLogged <= $DeadTime);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetDeadTime():
	int {

		$Now = Common\Date::Unixtime();
		$Time = ($Now - $this->TimeWindow);

		return $Time;
	}

	public function
	GetAttemptsRemaining():
	int {

		return $this->HitData->GetAttemptsRemaining();
	}

	public function
	GetWhenExpires():
	string {

		$When = new Common\Units\Timeframe(
			$this->GetDeadTime(),
			$this->HitData->GetTimeLogged()
		);

		return $When->Get();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetApp(Atlantis\Engine $App):
	static {

		$this->App = $App;

		return $this;
	}

	public function
	SetUserKey(string $UserKey):
	static {

		$this->UKey = $UserKey;

		////////

		if(isset($this->HitHash))
		unset($this->HitHash);

		return $this;
	}

	public function
	SetEndpointKey(string $EndpointKey):
	static {

		$this->EKey = $EndpointKey;

		////////

		if(isset($this->HitHash))
		unset($this->HitHash);

		return $this;
	}

	public function
	SetTimeWindow(int $Seconds):
	static {

		$this->TimeWindow = max(0, $Seconds);

		return $this;
	}

	public function
	SetMaxAttempts(int $Count):
	static {

		$this->MaxAttempts = max(1, $Count);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(Atlantis\Engine $App, string $UserKey, string $Endpoint, int $TimeWindow=60, int $MaxAttempts=3):
	static {

		$Output = new static(
			$App,
			$UserKey,
			$Endpoint,
			$TimeWindow,
			$MaxAttempts
		);

		return $Output;
	}

};
