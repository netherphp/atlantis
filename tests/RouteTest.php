<?php

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

use PHPUnit\Framework\TestCase;

define('ProjectRewt', Nether\Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

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

		//print_r($Handler);

		$this->AssertEquals('GET', $Handler->Verb);
		$this->AssertEquals('Routes\\Home', $Handler->Class);
		$this->AssertEquals('Index', $Handler->Method);

		return;
	}

}