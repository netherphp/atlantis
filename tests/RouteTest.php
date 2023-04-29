<?php

use PHPUnit\Framework\TestCase;

define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

class RouteTest
extends TestCase {

	static public function
	ConfigureRequest(string $Method='GET', string $Host='atlantis.dev', string $Path='/'):
	void {

		$_SERVER['REQUEST_METHOD'] = $Method;
		$_SERVER['HTTP_HOST'] = $Host;
		$_SERVER['REQUEST_URI'] = $Path;

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestBasic():
	void {

		static::ConfigureRequest();

		$App = new Nether\Atlantis\Engine(ProjectRewt);
		$App->Run();

		Nether\Common\Dump::Var($App->Router->GetHandlers());
		Nether\Common\Dump::Var($App->Router->Request);
		Nether\Common\Dump::Var($App->Router->Response);

		$this->AssertTrue(TRUE);

		return;
	}


}