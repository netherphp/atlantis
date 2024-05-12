<?php

namespace Nether\Atlantis;

use Nether\Console;

class TerminalApp
extends Console\Client {

	protected Engine
	$App;

	protected function
	OnReady():
	void {

		// one interesting thing of note here is that when given the
		// foreign path there is no version checking currently and that
		// code is going to ask for classes that will be served from the
		// local autoloader.

		$ProjectRoot = (NULL
			?? $this->GetOption('Atlantis')
			?? $this->GetOption('AppRoot')
		);

		////////

		if(is_string($ProjectRoot))
		$ProjectRoot = realpath($ProjectRoot);

		if(!is_string($ProjectRoot))
		$ProjectRoot = Util::GetBinProjectDirectory($this->File);

		////////

		$this->App = new Engine($ProjectRoot);

		return;
	}

};
