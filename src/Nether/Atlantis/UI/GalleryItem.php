<?php

namespace Nether\Atlantis\UI;

use Nether\Common;

class GalleryItem {

	public string
	$Area;

	public mixed
	$Item;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Area, mixed $Item) {

		$this->Area = $Area;
		$this->Item = $Item;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetArea():
	string {

		if(!isset($this->Area))
		throw new Common\Error\RequiredDataMissing('Area', 'string');

		return Gallery::ExpandAreaPath($this->Area);
	}

	public function
	SetArea(string $Area):
	static {

		$this->Area = $Area;

		return $this;
	}

	public function
	GetItem():
	mixed {

		if(!isset($this->Item))
		throw new Common\Error\RequiredDataMissing('Item', 'mixed');

		return $this->Item;
	}

	public function
	SetItem(mixed $Item):
	static {

		$this->Item = $Item;

		return $this;
	}


};
