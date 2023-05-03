<?php

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\User;

use PHPUnit\Framework\TestCase;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

class TestUserNormal
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

class TestUserAdmin
extends TestUserNormal {

	public function
	__Construct() {

		parent::__Construct();
		$this->Admin = 1;

		return;
	}

}

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class RouteTest
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

		return $App;
	}

	static public function
	RunAtlantis(Atlantis\Engine $App):
	string {

		ob_start();

		$App->Run();

		return ob_get_clean();
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

		$App = static::BuildAtlantis();
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Home', $Handler->Class);
		$this->AssertEquals('Index', $Handler->Method);
		$this->AssertNull($App->User);

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRouteDoesNotExist():
	void {

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/does-not-exist');

		$App = static::BuildAtlantis();
		$Out = static::RunAtlantis($App);

		// @todo 2023-05-02 the code from the handler needs to be set in
		// the response by the avenue router and this needs to be updated
		// to check CodeNotFound afterwards.

		$this->AssertEquals(
			Avenue\Response::CodeOK,
			$App->Router->Response->Code
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	// RouteAccessType annotation tests ////////////////////////////

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRoutePublic():
	void {

		// this is an open route that has no requirements for access.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/public');

		$App = static::BuildAtlantis();
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals(Avenue\Response::CodeOK, $App->Router->Response->Code);
		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('HandlePublic', $Handler->Method);

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRouteRequireUserFail():
	void {

		// without a valid user session this route should terminate itself
		// by redirecting to the login page.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/user');

		$App = static::BuildAtlantis();
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('__RewireDoNothing', $Handler->Method);
		$this->AssertFalse(str_contains(
			$App->Router->Response->Content,
			'user access granted'
		));

		$this->AssertEquals(Avenue\Response::CodeFound, $App->Router->Response->Code);
		$this->AssertTrue($App->Router->Response->Headers->HasKey('location'));

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRouteRequireUserSuccess():
	void {

		// with a valid user session this route should run without any
		// hinderance and return a normal result.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/user');

		$App = static::BuildAtlantis();
		$App->User = new TestUserNormal;
		$Out = static::RunAtlantis($App);

		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('HandleRequireUser', $Handler->Method);
		$this->AssertTrue(str_contains(
			$App->Router->Response->Content,
			'user access granted'
		));

		$this->AssertEquals(Avenue\Response::CodeOK, $App->Router->Response->Code);
		$this->AssertFalse($App->Router->Response->Headers->HasKey('location'));

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRouteRequireAdminFail1():
	void {

		// without a valid user admin session this route should terminate
		// itself with a redirect to the login page.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/admin');

		$App = static::BuildAtlantis();
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('__RewireDoNothing', $Handler->Method);
		$this->AssertFalse(str_contains(
			$App->Router->Response->Content,
			'admin access granted'
		));

		$this->AssertEquals(Avenue\Response::CodeFound, $App->Router->Response->Code);
		$this->AssertTrue($App->Router->Response->Headers->HasKey('location'));

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRouteRequireAdminFail2():
	void {

		// with a valid user but not a valid admin session this route
		// should terminate itself with a forbidden.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/admin');

		$App = static::BuildAtlantis();
		$App->User = new TestUserNormal;
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('__RewireDoNothing', $Handler->Method);
		$this->AssertFalse(str_contains(
			$App->Router->Response->Content,
			'admin access granted'
		));

		$this->AssertEquals(Avenue\Response::CodeForbidden, $App->Router->Response->Code);
		$this->AssertFalse($App->Router->Response->Headers->HasKey('location'));

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRouteRequireAdminSuccess():
	void {

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/admin');

		$App = static::BuildAtlantis();
		$App->User = new TestUserAdmin;
		$Out = static::RunAtlantis($App);

		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('HandleRequireAdmin', $Handler->Method);
		$this->AssertTrue(str_contains(
			$App->Router->Response->Content,
			'admin access granted'
		));

		$this->AssertEquals(Avenue\Response::CodeOK, $App->Router->Response->Code);
		$this->AssertFalse($App->Router->Response->Headers->HasKey('location'));

		return;
	}

}