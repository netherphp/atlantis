<?php

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;
use Nether\Database;

#[Common\Meta\Date('2023-11-20')]
class Pager
extends Surface\Element {

	////////////////////////////////////////////////////////////////
	// OVERRIDES: Surface\Element /////////////////////////////////

	public string
	$Area = 'elements/pager/main';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public int
	$Page = 1;

	public int
	$PageCount = 1;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromSurfaceWithResultSet(Surface\Engine $Surface, Database\ResultSet $Results):
	static {

		$Output = new static($Surface);
		$Output->Page = $Results->Page;
		$Output->PageCount = $Results->PageCount;

		return $Output;
	}

}
