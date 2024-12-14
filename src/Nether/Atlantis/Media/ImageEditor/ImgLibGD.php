<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media\ImageEditor;

################################################################################
################################################################################

class ImgLibGD
extends ImgLib {

	public function
	Open(string $Filename):
	static {
		throw new \Nether\Atlantis\Error\MethodUnimplemented(__METHOD__);
		return $this;
	}

	public function
	Close():
	static {
		throw new \Nether\Atlantis\Error\MethodUnimplemented(__METHOD__);
		return $this;
	}

	public function
	Save(string $Filename):
	static {
		throw new \Nether\Atlantis\Error\MethodUnimplemented(__METHOD__);
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	IsOpen():
	bool {
		throw new \Nether\Atlantis\Error\MethodUnimplemented(__METHOD__);
		return FALSE;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Fit(int $W, int $H):
	static {
		throw new \Nether\Atlantis\Error\MethodUnimplemented(__METHOD__);
		return $this;
	}

	public function
	RemoveMetadata():
	static {
		throw new \Nether\Atlantis\Error\MethodUnimplemented(__METHOD__);
		return $this;
	}

};
