<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

################################################################################
################################################################################

#[Common\Meta\Date('2023-11-24')]
class Collapser
extends Surface\Element {

	public string
	$Area = 'elements/collapser/main';

	public string
	$Content = '';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromSurfaceWithContent(Surface\Engine $Surface, string $Title, string $Content):
	static {

		$Output = new static($Surface);
		$Output->Title = $Title;
		$Output->Content = $Content;

		return $Output;
	}

};
