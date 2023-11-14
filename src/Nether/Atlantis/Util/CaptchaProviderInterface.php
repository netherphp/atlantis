<?php

namespace Nether\Atlantis\Util;

use Nether\Atlantis;

interface CaptchaProviderInterface {

	public function
	__Construct(Atlantis\Engine $App);

	public function
	IsValid():
	bool;

	public function
	GetHTML():
	string;

}
