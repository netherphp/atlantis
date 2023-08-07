<?php

namespace Nether\Atlantis\Util\Tests;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

use PHPUnit\Framework\TestCase;

class TestCasePU9
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

}
