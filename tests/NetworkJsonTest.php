<?php

use Nether\Atlantis;

if(!defined('ProjectRewt'))
define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class NetworkJsonTest
extends Atlantis\Util\Tests\TestCasePU9 {

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestBasic():
	void {

		$this->AssertTrue(TRUE);
		return;
	}

}