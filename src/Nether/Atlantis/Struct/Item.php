<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

#[Common\Meta\Date('2023-11-22')]
#[Common\Meta\Info('This is a super generic Item that fits the most basic bill of something that needs to be an object but probably is not worth it.')]
class Item
extends Common\Prototype {

	#[Common\Meta\PropertyListable]
	public mixed
	$ID = NULL;

	public function
	GetID():
	mixed {

		return $this->ID;
	}

	public function
	SetID(mixed $ID):
	static {

		$this->ID = $ID;
		return $this;
	}

	public function
	IsTheID(mixed $ID):
	bool {

		return $this->ID === $ID;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyListable]
	public ?string
	$UUID = NULL;

	public function
	GetUUID():
	?string {

		return $this->UUID;
	}

	public function
	SetUUID(?string $UUID):
	static {

		$this->UUID = $UUID;
		return $this;
	}

	public function
	IsTheUUID(?string $UUID):
	bool {

		return $this->UUID === $UUID;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyListable]
	public ?string
	$Title = NULL;

	public function
	GetTitle():
	?string {

		return $this->Title;
	}

	public function
	SetTitle(?string $Title):
	static {

		$this->Title = $Title;
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyListable]
	public ?string
	$URL = NULL;

	public function
	GetURL():
	?string {

		return $this->URL;
	}

	public function
	SetURL(?string $URL):
	static {

		$this->URL = $URL;
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyListable]
	public ?string
	$ImageURL = NULL;

	public function
	GetImageURL():
	?string {

		return $this->ImageURL;
	}

	public function
	SetImageURL(?string $ImageURL):
	static {

		$this->ImageURL = $ImageURL;
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyListable]
	public ?string
	$OnClick = NULL;

	public function
	GetOnClick():
	?string {

		return $this->OnClick;
	}

	public function
	SetOnClick(?string $OnClick):
	static {

		$this->OnClick = $OnClick;
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetDataAttr():
	Common\Datastore {

		$Output = new Common\Datastore;

		if($this->ID !== NULL)
		$Output['data-item-id'] = $this->ID;

		if($this->UUID !== NULL)
		$Output['data-item-uuid'] = $this->UUID;

		return $Output;
	}

	public function
	GetDataAttrAsHTML():
	string {

		$Output = $this->GetDataAttr();

		$Output->RemapKeyValue(
			fn(string $K, mixed $V)
			=> sprintf('%s="%s"', htmlentities($K), htmlentities($V))
		);

		return $Output->Join(' ');
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(?string $ID=NULL, ?string $UUID=NULL, ?string $Title=NULL, ?string $URL=NULL):
	static {

		$Output = new static([
			'ID'    => $ID,
			'UUID'  => $UUID,
			'Title' => $Title,
			'URL'   => $URL
		]);

		return $Output;
	}

}
