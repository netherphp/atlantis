<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

#[Common\Meta\Date('2023-11-22')]
#[Common\Meta\Info('This is a super generic Item that fits the most basic bill of something that needs to be an object but probably is not worth it.')]
class Item
extends Common\Prototype {

	#[Common\Meta\Date('2023-11-22')]
	#[Common\Meta\PropertyListable]
	public mixed
	$ID = NULL;

	public function
	HasID():
	bool {

		return ($this->ID !== NULL);
	}

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

	#[Common\Meta\Date('2023-11-22')]
	#[Common\Meta\PropertyListable]
	public ?string
	$UUID = NULL;

	public function
	HasUUID():
	bool {

		return ($this->UUID !== NULL);
	}

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

	#[Common\Meta\Date('2023-11-22')]
	#[Common\Meta\PropertyListable]
	public ?string
	$Title = NULL;

	public function
	HasTitle():
	bool {

		return ($this->Title !== NULL);
	}

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

	#[Common\Meta\Date('2023-11-22')]
	#[Common\Meta\PropertyListable]
	public ?string
	$URL = NULL;

	public function
	HasURL():
	bool {

		return ($this->URL !== NULL);
	}

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

	#[Common\Meta\Date('2023-11-22')]
	#[Common\Meta\PropertyListable]
	public ?string
	$ImageURL = NULL;

	public function
	HasImageURL():
	bool {

		return ($this->ImageURL !== NULL);
	}

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

	#[Common\Meta\Date('2023-11-22')]
	#[Common\Meta\PropertyListable]
	public ?string
	$OnClick = NULL;

	public function
	HasOnClick():
	bool {

		return ($this->OnClick !== NULL);
	}

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

	#[Common\Meta\Date('2023-11-24')]
	#[Common\Meta\PropertyListable]
	public ?string
	$Icon = NULL;

	public function
	HasIcon():
	bool {

		return ($this->Icon !== NULL);
	}

	public function
	GetIcon():
	?string {

		return $this->Icon;
	}

	public function
	GetIconAsHTML():
	string {

		$Output = static::IconToHTML(
			$this->Icon,
			($this->Info ?? $this->Title)
		);

		return $Output;
	}

	public function
	SetIcon(?string $Icon):
	static {

		$this->Icon = $Icon;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-11-24')]
	#[Common\Meta\PropertyListable]
	public ?string
	$Info = NULL;

	public function
	HasInfo():
	bool {

		return ($this->Info !== NULL);
	}

	public function
	GetInfo():
	?string {

		return $this->Info;
	}

	public function
	SetInfo(?string $Info):
	static {

		$this->Info = $Info;
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	use
	Common\Package\ClassListPackage;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyFactory('FromArray', 'ExtraData')]
	public array|Common\Datastore
	$ExtraData = [];

	public function
	GetExtraData():
	Common\Datastore {

		if(is_array($this->ExtraData))
		$this->ExtraData = Common\Datastore::FromArray($this->ExtraData);

		////////

		return $this->ExtraData;
	}

	public function
	GetExtraDataArray():
	array {

		return $this->GetExtraData()->GetData();
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
	New(?string $ID=NULL, string|bool|NULL $UUID=TRUE, ?string $Title=NULL, ?string $URL=NULL, ?string $Icon=NULL, ?string $ImageURL=NULL, ?string $Info=NULL, ?array $Classes=NULL, ?array $ExtraData=NULL):
	static {

		$Output = new static([
			'ID'        => $ID,
			'UUID'      => (match(TRUE){
				(is_bool($UUID) && $UUID)
				=> Common\UUID::V7(),

				default
				=> $UUID
			}),
			'Title'     => $Title,
			'URL'       => $URL,
			'Icon'      => $Icon,
			'ImageURL'  => $ImageURL,
			'Info'      => $Info,
			'Classes'   => $Classes ?? [],
			'ExtraData' => $ExtraData ?? []
		]);

		return $Output;
	}

	static public function
	IconToHTML(string $Icon, ?string $AltText=NULL):
	string {

		$Icon ??= '';
		$AltText ??= '';

		$Output = match(TRUE) {
			str_starts_with($Icon, 'mdi-')
			=> sprintf(
				'<i class="mdi %s" title="%s"></i>',
				$Icon,
				$AltText
			),

			str_starts_with($Icon, 'si-')
			=> sprintf(
				'<i class="si %s" title="%s"></i>',
				$Icon,
				$AltText
			),

			str_starts_with($Icon, 'img=')
			=> sprintf(
				'<img src="%s" title="%s" />',
				preg_replace('#^img=#', '', $Icon),
				$AltText
			),

			default
			=> $Icon
		};

		return $Output;
	}

}
