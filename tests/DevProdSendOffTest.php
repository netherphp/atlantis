<?php

use Nether\Atlantis;

if(!defined('ProjectRewt'))
define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class DevProdSendOffTest
extends Atlantis\Util\Tests\TestCasePU9 {

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestBasic():
	void {

		static::SetupAutoloader();
		static::SetupRequestEnv();
		static::RunAtlantis($App = static::BuildAtlantis());
		define('UNIT_TEST_GO_BRRRT_TTT', 1);

		// by default its should be disabled.

		$Send = new Atlantis\Util\DevProdSendOffMachine($App);
		$this->AssertFalse($Send->ShouldSendOff());

		// require admins.

		$App->Config[Atlantis\Key::ConfDevProdSendOff] = 1;
		$Send = new Atlantis\Util\DevProdSendOffMachine($App);

		$App->User = NULL;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new Atlantis\Util\Tests\TestUserNormal;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new Atlantis\Util\Tests\TestUserAdmin;
		$this->AssertFalse($Send->ShouldSendOff());

		// require users.

		$App->Config[Atlantis\Key::ConfDevProdSendOff] = 2;
		$Send = new Atlantis\Util\DevProdSendOffMachine($App);

		$App->User = NULL;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new Atlantis\Util\Tests\TestUserNormal;
		$this->AssertFalse($Send->ShouldSendOff());

		$App->User = new Atlantis\Util\Tests\TestUserAdmin;
		$this->AssertFalse($Send->ShouldSendOff());

		// require users with developer access.

		$App->Config[Atlantis\Key::ConfDevProdSendOff] = 3;
		$Send = new Atlantis\Util\DevProdSendOffMachine($App);

		$App->User = NULL;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new Atlantis\Util\Tests\TestUserNormal;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new Atlantis\Util\Tests\TestUserDeveloper;
		$this->AssertFalse($Send->ShouldSendOff());

		$App->User = new Atlantis\Util\Tests\TestUserAdmin;
		$this->AssertFalse($Send->ShouldSendOff());

		return;
	}

}
