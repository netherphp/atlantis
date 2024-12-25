<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Social;

use Nether\Common;

use Exception;

################################################################################
################################################################################

abstract class Service {

	static public string
	$Key = 'unknown';

	static public string
	$Name = 'Unnamed Social Media';

	static public string
	$Icon = 'mdi mdi-help-box';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public string
	$Service;

	public string
	$Handle;

	public int
	$NumFollowers;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct() {

		$this->Service = static::$Key;

		return;
	}

	////////////////////////////////////////////////////////////////
	// psuedo abstract methods /////////////////////////////////////

	public function
	Fetch():
	static {

		throw new Common\Error\MethodUnimplemented(__CLASS__, __FUNCTION__);
		return $this;
	}

	public function
	GetURL():
	string {

		return '---url---';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetKey():
	string {

		return static::$Key;
	}

	public function
	GetName():
	string {

		return static::$Name;
	}

	public function
	GetIcon():
	string {

		return static::$Icon;
	}

	public function
	SetService(string $Service):
	static {

		$this->Service = $Service;

		return $this;
	}

	public function
	SetHandle(string $Handle):
	static {

		$this->Handle = $Handle;

		return $this;
	}

	public function
	SetNumFollowers(int $Num):
	static {

		$this->NumFollowers = $Num;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	PushToDatabase():
	static {

		PingDataRow::Insert([
			'Service'      => $this->Service,
			'Handle'       => $this->Handle,
			'NumFollowers' => $this->NumFollowers
		]);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFactory(string $Service):
	static {

		return match($Service) {

			Services\Bluesky::$Key
			=> new Services\Bluesky,

			Services\Mastodon::$Key
			=> new Services\Mastodon,

			default
			=> throw new Exception('unsupported social media')

		};
	}

};
