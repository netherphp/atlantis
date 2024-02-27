<?php

use Nether\Atlantis;

if(!defined('ProjectRewt'))
define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class RewriteUrlTest
extends Atlantis\Util\Tests\TestCasePU9 {

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestDev():
	void {

		$_ENV['ATLANTIS.ENV'] = 'dev';
		static::SetupAutoloader();
		static::SetupRequestEnv();
		static::RunAtlantis($App = static::BuildAtlantis());
		define('UNIT_TEST_GO_BRRRT_TTT', 1);

		$this->AssertEquals(
			'https://pegasusgate.net/test',
			$App->RewriteURL('https://pegasusgate.net/test')
		);

		$this->AssertEquals(
			'https://www.pegasusgate.net/test',
			$App->RewriteURL('https://www.pegasusgate.net/test')
		);

		$this->AssertEquals(
			'https://dev.pegasusgate.net/test',
			$App->RewriteURL('atl://pegasusgate.net/test')
		);

		$this->AssertEquals(
			'https://dev.pegasusgate.net/test',
			$App->RewriteURL('atl://www.pegasusgate.net/test')
		);

		unset($_ENV['ATLANTIS.ENV']);
		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestProd():
	void {

		$_ENV['ATLANTIS.ENV'] = 'prod';
		static::SetupAutoloader();
		static::SetupRequestEnv();
		static::RunAtlantis($App = static::BuildAtlantis());
		define('UNIT_TEST_GO_BRRRT_TTT', 1);

		$this->AssertEquals(
			'https://pegasusgate.net/test',
			$App->RewriteURL('https://pegasusgate.net/test')
		);

		$this->AssertEquals(
			'https://www.pegasusgate.net/test',
			$App->RewriteURL('https://www.pegasusgate.net/test')
		);

		$this->AssertEquals(
			'https://pegasusgate.net/test',
			$App->RewriteURL('atl://pegasusgate.net/test')
		);

		$this->AssertEquals(
			'https://www.pegasusgate.net/test',
			$App->RewriteURL('atl://www.pegasusgate.net/test')
		);

		unset($_ENV['ATLANTIS.ENV']);
		return;
	}

}
