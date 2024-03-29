<?php

namespace Nether\Atlantis\UI;

use Nether\Common;
use Nether\Surface;

#[Common\Meta\Date('2023-09-15')]
class Gallery
extends Surface\Element {

	////////////////////////////////////////////////////////////////
	// OVERRIDES: Surface\Element /////////////////////////////////

	public string
	$Area = 'elements/gallery/main';

	////////

	public function
	Render():
	string {

		if(!isset($this->ItemArea))
		throw new Common\Error\RequiredDataMissing('ItemArea', 'string');

		if(!isset($this->Items))
		throw new Common\Error\RequiredDataMissing('Items', 'Nether\\Common\\Datastore');

		return parent::Render();
	}

	static public function
	ExpandAreaPath(string $Area):
	string {

		if(str_starts_with($Area, '~/'))
		$Area = preg_replace('#^~/#', 'elements/gallery/', $Area);

		return $Area;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Batch Item Interface /////////////////////////////////

	public mixed
	$Parent = NULL;

	////////////////////////////////////////////////////////////////
	// LOCAL: Batch Item Interface /////////////////////////////////

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$Items;

	////////

	#[Common\Meta\Info('Get the list of items.')]
	public function
	GetItems():
	iterable {

		return $this->Items;
	}

	#[Common\Meta\Info('Get a list of items wrapped as SliderItems if they are not already.')]
	public function
	GetItemsWrapped():
	Common\Datastore {

		$Output = $this->Items->Map(function(mixed $Item) {
			if($Item instanceof GalleryItem)
			return $Item;

			return new GalleryItem($this->ItemArea, $Item);
		});

		return $Output;
	}

	public function
	SetItems(iterable $Items):
	static {

		$this->Items->SetData($Items);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Item Area Interface //////////////////////////////////

	public string
	$ItemArea;

	////////

	#[Common\Meta\Info('Get the default area for items.')]
	public function
	GetItemArea():
	string {

		if(!isset($this->ItemArea))
		throw new Common\Error\RequiredDataMissing('ItemArea', 'string');

		return $this->ExpandAreaPath($this->ItemArea);
	}

	#[Common\Meta\Info('Set the default area for items.')]
	public function
	SetItemArea(string $Area):
	static {

		$this->ItemArea = $Area;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Rendering API ////////////////////////////////////////

	public function
	RenderItem(mixed $Item, mixed $Key=NULL):
	string {

		if($Item instanceof GalleryItem)
		$Area = $Item->GetArea();
		else
		$Area = $this->GetItemArea();

		////////

		return $this->Surface->GetArea($Area, [
			'Element' => $this,
			'Key'     => $Key,
			'Item'    => $Item,
			'Parent'  => $this->Parent
		]);
	}

	public function
	RenderItems():
	string {

		$Key = NULL;
		$Item = NULL;
		$Output = '';

		foreach($this->GetItemsWrapped() as $Key => $Item)
		$Output .= $this->RenderItem($Item, $Key);

		return $Output;
	}

	public function
	PrintItem(mixed $Item):
	static {

		echo $this->RenderItem($Item);

		return $this;
	}

	public function
	PrintItems():
	static {

		echo $this->RenderItems();

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// LOCAL: Factory API //////////////////////////////////////////

	#[Common\Meta\Info('Ready an element to display a specified dataset.')]
	static public function
	FromDataset(Surface\Engine $Surface, iterable $Items=NULL, ?string $Area='~/item-photo'):
	static {

		$Output = new static($Surface);

		if($Items !== NULL)
		$Output->SetItems($Items);

		if($Area !== NULL)
		$Output->SetItemArea($Area);

		return $Output;
	}

}
