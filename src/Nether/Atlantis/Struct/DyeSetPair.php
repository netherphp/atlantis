<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Struct;

use Nether\Common;
use Nether\Dye;

################################################################################
################################################################################

class DyeSetPair
extends Common\Prototype {

	public string
	$Bg;

	public string
	$Fg;

	protected function
	OnReady(Common\Prototype\ConstructArgs $Raw):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	NewFG():
	string|Dye\Colour {

		try { $C = Dye\Colour::From($this->Fg); }
		catch(Dye\Error\InvalidColourFormat $Err) {
			return $this->Fg;
		}

		return Dye\Colour::From($this->Fg);
	}

	public function
	NewBG():
	string|Dye\Colour {

		try { $C = Dye\Colour::From($this->Bg); }
		catch(Dye\Error\InvalidColourFormat $Err) {
			return $this->Bg;
		}

		return Dye\Colour::From($this->Bg);
	}

};
