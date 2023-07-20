<?php

namespace Nether\Atlantis\Plugins;

use Nether\Atlantis;

class CommandLineExtension
implements CommandLineExtensionInterface {

	public function
	Get():
	iterable {

		$Output = [
			'backup.atl',
			'dev.atl',
			'cron.atl',
			'gfx.atl',
			'ssl.atl'
		];

		return $Output;
	}

}
