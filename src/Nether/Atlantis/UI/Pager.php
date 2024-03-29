<?php

namespace Nether\Atlantis\UI;

use Nether\Common;
use Nether\Surface;
use Nether\Database;

#[Common\Meta\Date('2023-11-20')]
class Pager
extends Surface\Element {

	public string
	$Area = 'elements/pager/main';

	public int
	$Page = 1;

	public int
	$PageCount = 1;

	public ?string
	$PrevURL = NULL;

	public ?string
	$NextURL = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetPage(int $Page):
	static {

		$this->Page = $Page;

		return $this;
	}

	public function
	SetPageCount(int $Count):
	static {

		$this->PageCount = $Count;

		return $this;
	}

	public function
	SetPrevURL(?string $URL):
	static {

		$this->PrevURL = $URL;

		return $this;
	}

	public function
	SetNextURL(?string $URL):
	static {

		$this->NextURL = $URL;

		return $this;
	}

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
