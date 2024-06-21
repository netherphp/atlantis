<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Plugin\Interfaces\Engine;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

#[Common\Meta\Date('2024-06-21')]
interface AppInstanceStaticInterface {

	static public function
	AppInstanceSet(Atlantis\Engine $App):
	void;

	static public function
	AppInstanceGet():
	?Atlantis\Engine;

};
