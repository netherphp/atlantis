<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Packages;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

#[Common\Meta\Date('2024-06-21')]
trait AppInstanceStatic {

	static protected ?Atlantis\Engine
	$AppInstance = NULL;

	static public function
	AppInstanceSet(?Atlantis\Engine $App):
	void {

		static::$AppInstance = $App;

		return;
	}

	static public function
	AppInstanceGet():
	?Atlantis\Engine {

		return static::$AppInstance;
	}

};
