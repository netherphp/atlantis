<?php

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\User;

use PHPUnit\Framework\TestCase;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

if(!defined('ProjectRewt'))
define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

class TestUserNormal2
extends User\EntitySession {

	public function
	__Construct() {

		parent::__Construct([
			'ID'          => 42,
			'Alias'       => 'geordi-laforge',
			'TimeCreated' => time(),
			'TimeBanned'  => 0,
			'Activated'   => 1,
			'Admin'       => 0
		]);

		return;
	}

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->AccessTypes = new Common\Datastore;

		parent::OnReady($Args);

		return;
	}

}

class TestUserGifted2
extends User\EntitySession {

	public function
	__Construct() {

		parent::__Construct([
			'ID'          => 42,
			'Alias'       => 'geordi-laforge',
			'TimeCreated' => time(),
			'TimeBanned'  => 0,
			'Activated'   => 1,
			'Admin'       => 0
		]);

		return;
	}

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->AccessTypes = new Common\Datastore([
			Atlantis\Key::AccessDeveloper
			=> new User\EntityAccessType([ 'Key'=> Atlantis\Key::AccessDeveloper, 'Value'=>1 ])
		]);

		parent::OnReady($Args);

		return;
	}

}

class TestUserAdmin2
extends TestUserNormal2 {

	public function
	__Construct() {

		parent::__Construct();
		$this->Admin = 1;

		return;
	}

}

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class DevProdSendOffTest
extends TestCase {

	static public function
	Autoload(string $Class):
	void {

		if(str_starts_with($Class, 'Routes')) {
			$Class = preg_replace('#^Routes\\\\#', 'routes\\', $Class);

			require(Common\Filesystem\Util::Pathify(
				ProjectRewt,
				sprintf(
					'%s.php',
					str_replace('\\', '/', $Class)
				)
			));
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	SetupAutoloader():
	void {

		spl_autoload_register('RouteTest::Autoload');

		return;
	}

	static public function
	SetupRequestEnv(string $Method='GET', string $Host='atlantis.dev', string $Path='/'):
	void {

		$_SERVER['REQUEST_METHOD'] = $Method;
		$_SERVER['HTTP_HOST'] = $Host;
		$_SERVER['REQUEST_URI'] = $Path;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	BuildRouteScanner():
	Avenue\RouteScanner {

		$Scan = new Avenue\RouteScanner(Common\Filesystem\Util::Pathify(
			ProjectRewt, 'routes'
		));

		return $Scan;
	}

	static public function
	BuildAtlantis():
	Atlantis\Engine {

		$App = new Atlantis\Engine(ProjectRewt);
		$Scan = static::BuildRouteScanner();

		$App->Router->AddHandlers($Scan->Generate());
		//$App->Router->Response->HTTP = FALSE;

		return $App;
	}

	static public function
	RunAtlantis(Atlantis\Engine $App):
	string {

		ob_start();
		$App->Run();
		$Output = ob_get_clean();

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

		$App->User = new TestUserNormal2;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new TestUserAdmin2;
		$this->AssertFalse($Send->ShouldSendOff());

		// require users.

		$App->Config[Atlantis\Key::ConfDevProdSendOff] = 2;
		$Send = new Atlantis\Util\DevProdSendOffMachine($App);

		$App->User = NULL;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new TestUserNormal2;
		$this->AssertFalse($Send->ShouldSendOff());

		$App->User = new TestUserAdmin2;
		$this->AssertFalse($Send->ShouldSendOff());

		// require users with access.

		$App->Config[Atlantis\Key::ConfDevProdSendOff] = 3;
		$Send = new Atlantis\Util\DevProdSendOffMachine($App);

		$App->User = NULL;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new TestUserNormal2;
		$this->AssertTrue($Send->ShouldSendOff());

		$App->User = new TestUserGifted2;
		$this->AssertFalse($Send->ShouldSendOff());

		$App->User = new TestUserAdmin2;
		$this->AssertFalse($Send->ShouldSendOff());

		return;
	}


}