<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Social;

use Nether\Common;

################################################################################
################################################################################

class Service {

	public string
	$Service;

	public string
	$Handle;

	public int
	$NumFollowers;

	////////////////////////////////////////////////////////////////
	// psuedo abstract methods /////////////////////////////////////

	public function
	Fetch():
	static {

		throw new Common\Error\MethodUnimplemented(__CLASS__, __FUNCTION__);
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
	FromHandle(string $Handle):
	static {

		$Output = new static;
		$Output->SetHandle($Handle);

		return $Output;
	}

};
