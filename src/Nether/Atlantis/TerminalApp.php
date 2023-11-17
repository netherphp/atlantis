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

		$ProjectRoot = $this->GetOption('atlantis');

		////////

		if(is_string($ProjectRoot))
		$ProjectRoot = realpath($ProjectRoot);

		if(!is_string($ProjectRoot))
		$ProjectRoot = Util::GetBinProjectDirectory($this->File);

		////////

		$this->App = new Engine($ProjectRoot);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// these should probably all move to console client as they use nothing
	// of the atlantis extension and at some point ill get angsty on the
	// other apps i build having to re-add these lol.

	protected function
	PrintH1(string $Text):
	static {

		$this->PrintLn($this->FormatH1($Text));

		return $this;
	}

	protected function
	PrintH2(string $Text):
	static {

		$this->PrintLn($this->FormatH2($Text));

		return $this;
	}

	protected function
	PrintH3(string $Text):
	static {

		$this->PrintLn($this->FormatH3($Text));

		return $this;
	}

	protected function
	PrintH4(string $Text):
	static {

		$this->PrintLn($this->FormatH4($Text));

		return $this;
	}

	protected function
	PrintAppHeader(string $Title):
	static {

		$this->PrintLn($this->FormatH1(sprintf(
			'%s: %s',
			$this->AppInfo->Name,
			$Title
		)));

		return $this;
	}

	public function
	PrintBulletList(iterable $List):
	static {

		$this->PrintLn($this->FormatBulletList($List), 2);

		return $this;
	}

	protected function
	PrintOK(?string $More=NULL, ?string $Yell='OK'):
	static {

		$Msg = match(TRUE) {
			($More !== NULL)
			=> "{$Yell}: {$More}",

			default
			=> $Yell
		};

		$this->PrintLn($this->FormatHeaderPoint(
			$Msg, Console\Theme::OK
		), 2);

		return $this;
	}

	protected function
	PrintError(?string $More=NULL, ?string $Yell='ERROR'):
	static {

		$Msg = match(TRUE) {
			($More !== NULL)
			=> "{$Yell}: {$More}",

			default
			=> $Yell
		};

		$this->PrintLn($this->FormatHeaderPoint(
			$Msg, Console\Theme::Error
		), 2);

		return $this;
	}

	protected function
	PrintStatusMuted(string $Msg):
	static {

		$this->PrintLn($this->FormatHeaderPoint(
			$Msg, Console\Theme::Muted
		), 2);

		return $this;
	}

	protected function
	PrintStatusAlert(string $Msg):
	static {

		$this->PrintLn($this->FormatHeaderPoint(
			$Msg, Console\Theme::Alert
		), 2);

		return $this;
	}

	protected function
	PrintStatusWarning(string $Msg):
	static {

		$this->PrintLn($this->FormatHeaderPoint(
			$Msg, Console\Theme::Warning
		), 2);

		return $this;
	}

};
