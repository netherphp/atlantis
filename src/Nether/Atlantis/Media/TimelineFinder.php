<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

#[Common\Meta\Date('2024-11-22')]
class TimelineFinder
extends Atlantis\Struct\PrototypeFindOptions {

	public ?int
	$ID = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(?int $ID=NULL):
	static {

		$Args = func_get_args();
		$Output = new static;
		$K = NULL;
		$V = NULL;

		////////

		foreach($Args as $K=> $V) {
			if(property_exists($Output, $K))
			$Output->{$K} = $V;
		}

		return $Output;
	}

};
