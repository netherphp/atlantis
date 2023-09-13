<?php

namespace Nether\Atlantis\UI;

use Nether\Common;
use Nether\Surface;

class Slider
extends Surface\Element {

	public string
	$Area = 'elements/slider';

	public Common\Datastore
	$Items;

	public string
	$ItemArea;

	////////////////////////////////////////////////////////////////
	// OVERRIDES: Common\Prototype /////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->Items = new Common\Datastore;

		return;
	}

	////////////////////////////////////////////////////////////////
	// OVERRIDES: Surface\Element //////////////////////////////////

	public function
	Render():
	string {

		if(!isset($this->ItemArea))
		throw new Common\Error\RequiredDataMissing('ItemArea', 'string');

		if(!isset($this->Items))
		throw new Common\Error\RequiredDataMissing('Items', 'Nether\\Common\\Datastore');

		return parent::Render();
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Batch Item Interface /////////////////////////////////

	public function
	GetItems():
	iterable {

		return $this->Items;
	}

	public function
	GetItemsWrapped():
	Common\Datastore {

		$Output = $this->Items->Map(function(mixed $Item) {
			if($Item instanceof SliderItem)
			return $Item;

			return new SliderItem($this->ItemArea, $Item);
		});

		return $Output;
	}

	public function
	SetItems(iterable $Items):
	static {

		$this->Items->SetData($Items);

		return $this;
	}

	public function
	RenderItems():
	string {

		$Item = NULL;
		$Output = '';

		foreach($this->GetItemsWrapped() as $Item)
		$Output .= $this->RenderItem($Item);

		return $Output;
	}

	public function
	PrintItems():
	static {

		echo $this->RenderItems();
		return $this;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Singular Item Interface //////////////////////////////

	public function
	RenderItem(mixed $Item):
	string {

		if($Item instanceof SliderItem)
		$Area = static::ExpandAreaPath($Item->Area);
		else
		$Area = $this->GetItemArea();

		////////

		return $this->Surface->GetArea($Area, [
			'Element' => $this,
			'Item'    => $Item
		]);
	}

	public function
	PrintItem(mixed $Item):
	static {

		echo $this->RenderItem($Item);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Item Area Interface //////////////////////////////////

	public function
	GetItemArea():
	string {

		if(!isset($this->ItemArea))
		throw new Common\Error\RequiredDataMissing('ItemArea', 'string');

		return static::ExpandAreaPath($this->ItemArea);
	}

	public function
	SetItemArea(string $Area):
	static {

		$this->ItemArea = $Area;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Factory API //////////////////////////////////////////

	static public function
	FromDataset(Surface\Engine $Surface, iterable $Items, ?string $Area=NULL):
	static {

		$Output = new static($Surface);
		$Output->SetItems($Items);

		if($Area !== NULL)
		$Output->SetItemArea($Area);

		return $Output;
	}

}
