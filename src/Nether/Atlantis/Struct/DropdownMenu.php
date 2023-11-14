<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;

class DropdownMenu
extends Common\Prototype {

	public ?string
	$Title = NULL;

	public ?string
	$Icon = NULL;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$Items;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetIcon():
	string {

		if(!$this->Icon)
		return '';

		$Output = match(TRUE) {
			(str_starts_with($this->Icon, 'mdi-'))
			=> "mdi {$this->Icon}",

			(str_starts_with($this->Icon, 'fa-'))
			=> "fa fw {$this->Icon}",

			(str_starts_with($this->Icon, 'fab-'))
			=> "fab fw {$this->Icon}",

			(str_starts_with($this->Icon, 'far-'))
			=> "far fw {$this->Icon}",

			default
			=> $this->Icon
		};

		if(!$this->Title)
		$Output .= ' mr-0';

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ItemPush(iterable|DropdownItem $Item):
	static {

		if(is_iterable($Item))
		$this->Items->MergeRight($Item);

		else
		$this->Items->Push($Item);

		return $this;
	}

	public function
	ItemNew(?string $Title=NULL, ?string $Icon=NULL, ?string $URL=NULL, iterable $Attr=[], int $Warn=0, bool $If=TRUE):
	static {

		if($If)
		$this->ItemPush(DropdownItem::New(
			Title: $Title,
			Icon: $Icon,
			URL: $URL,
			Attr: $Attr,
			Warn: $Warn
		));

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(?string $Title=NULL, ?string $Icon='mdi-cog'):
	static {

		$Output = new static;

		////////

		$Output->Title = $Title;
		$Output->Icon = $Icon;

		////////

		return $Output;
	}

};
