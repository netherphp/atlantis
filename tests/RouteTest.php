<?php

use Nether\Atlantis;
use Nether\Avenue;
use Nether\User;

if(!defined('ProjectRewt'))
define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class RouteTest
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
			Avenue\Response::CodeNotFound,
			$App->Router->Response->Code
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	// RouteAccessTypeUser annotation tests ////////////////////////

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
		$App->User = new Atlantis\Util\Tests\TestUserNormal;
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

	////////////////////////////////////////////////////////////////
	// RouteAccessTypeAdmin annotation tests ///////////////////////

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
		$App->User = new Atlantis\Util\Tests\TestUserNormal;
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
		$App->User = new Atlantis\Util\Tests\TestUserAdmin;
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

	////////////////////////////////////////////////////////////////
	// RouteAccessType w/ Type annotation tests ////////////////////

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestRouteRequireAccessTypeFail1():
	void {

		// without a valid user session this route should terminate itself
		// by redirecting to the login page.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/at1');

		$App = static::BuildAtlantis();
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('__RewireDoNothing', $Handler->Method);
		$this->AssertFalse(str_contains(
			$App->Router->Response->Content,
			'user access with type granted'
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
	TestRouteRequireAccessTypeFail2():
	void {

		// with a valid user but no defined access type this route should
		// terminate with a forbidden.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/at1');

		$App = static::BuildAtlantis();
		$App->User = new Atlantis\Util\Tests\TestUserNormal;
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('__RewireDoNothing', $Handler->Method);
		$this->AssertFalse(str_contains(
			$App->Router->Response->Content,
			'user access with type granted'
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
	TestRouteRequireAccessTypeFail3():
	void {

		// with a valid user but an invalid access type for the requirement
		// it should terminate with a forbidden.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/at1');

		$App = static::BuildAtlantis();
		$App->User = new Atlantis\Util\Tests\TestUserNormal;
		$App->User->GetAccessTypes()->Push('AccessType1', -1);
		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('__RewireDoNothing', $Handler->Method);
		$this->AssertFalse(str_contains(
			$App->Router->Response->Content,
			'user access with type granted'
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
	TestRouteRequireAccessTypeFail4():
	void {

		// with a valid user but an invalid access type for the requirement
		// it should terminate with a forbidden.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/at1');

		$App = static::BuildAtlantis();
		$App->User = new Atlantis\Util\Tests\TestUserNormal;

		$App->User->GetAccessTypes()->Shove(
			'AccessType1',
			new User\EntityAccessType([
				'Key'   => 'AccessType1',
				'Value' => -1
			])
		);

		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('__RewireDoNothing', $Handler->Method);
		$this->AssertFalse(str_contains(
			$App->Router->Response->Content,
			'user access with type granted'
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
	TestRouteRequireAccessTypeSuccess():
	void {

		// with a valid user with a valid access type this route should
		// execute itself unhindered.

		static::SetupAutoloader();
		static::SetupRequestEnv(Path: '/test/at1');

		$App = static::BuildAtlantis();
		$App->User = new Atlantis\Util\Tests\TestUserNormal;

		$App->User->GetAccessTypes()->Shove(
			'AccessType1',
			new User\EntityAccessType([
				'Key'   => 'AccessType1',
				'Value' => 1
			])
		);

		$Out = static::RunAtlantis($App);
		$Handler = $App->Router->GetCurrentHandler();

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Test', $Handler->Class);
		$this->AssertEquals('HandleRequireAT1', $Handler->Method);
		$this->AssertTrue(str_contains(
			$App->Router->Response->Content,
			'user access with type granted'
		));

		$this->AssertEquals(Avenue\Response::CodeOK, $App->Router->Response->Code);
		$this->AssertFalse($App->Router->Response->Headers->HasKey('location'));

		return;
	}

}
