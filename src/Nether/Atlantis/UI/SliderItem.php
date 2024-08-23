<?php

namespace Nether\Atlantis\UI;

use Nether\Common;

class SliderItem {

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
	// Local Item API //////////////////////////////////////////////

	// these methods are to allow you to throw things into the item set and
	// as long as they vaguely look like items hopefully it can figure out
	// what to show by inspecting it. some of these it is likely that this
	// item object will gain its own, like a title field, that it should
	// prefer before falling back to searching the item data.

	public function
	FetchItemProperty(string $Prop):
	?string {

		$Output = match(TRUE) {
			(is_iterable($this->Item) && isset($this->Item[$Prop]))
			=> $this->Item[$Prop],

			(is_object($this->Item) && property_exists($this->Item, $Prop))
			=> $this->Item->{$Prop},

			default
			=> NULL
		};

		return $Output;
	}

	public function
	GetItemTitle():
	?string {

		$Output = $this->FetchItemProperty('Title');

		return $Output;
	}

	public function
	GetItemURL():
	?string {

		$Output = $this->FetchItemProperty('URL');

		return $Output;
	}

	public function
	GetItemImageURL():
	?string {

		$Output = $this->FetchItemProperty('ImageURL');

		return $Output;
	}

	public function
	GetItemInfo():
	?string {

		$Output = $this->FetchItemProperty('Info');

		return $Output;
	}

	public function
	GetItemExtraData():
	Common\Datastore {

		$Output = NULL;

		////////

		if(is_object($this->Item)) {
			if(property_exists($this->Item, 'ExtraData'))
			$Output = $this->Item->ExtraData;
		}

		////////

		if(!$Output)
		$Output = new Common\Datastore;

		////////

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetArea():
	string {

		if(!isset($this->Area))
		throw new Common\Error\RequiredDataMissing('Area', 'string');

		return Slider::ExpandAreaPath($this->Area);
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
