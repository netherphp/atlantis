<?php

namespace Nether\Atlantis\UI;

class SliderItem {

	public string
	$Area;

	public mixed
	$Item;

	public function
	__Construct(string $Area, mixed $Item) {

		$this->Area = $Area;
		$this->Item = $Item;

		return;
	}

	public function
	GetItem():
	mixed {

		return $this->Item;
	}

	public function
	GetArea():
	string {

		return $this->Area;
	}

};
