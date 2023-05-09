<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Email;
use Nether\User;

class PageAPI
extends Atlantis\ProtectedAPI {

	#[Atlantis\Meta\RouteHandler('/api/page/entity')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleGet():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...));

		////////

		if(!$this->Data->ID)
		$this->Quit(1, 'no ID specified');

		////////

		$Page = Atlantis\Page\Entity::GetByID($this->Data->ID);

		if(!$Page)
		$this->Quit(2, 'page not found');

		$this->SetPayload($Page->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/entity', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandlePost():
	void {

		($this->Data)
		->Title(Common\Datafilters::TrimmedText(...))
		->Subtitle(Common\Datafilters::TrimmedTextNullable(...))
		->URL(Common\Datafilters::PathableKey(...));

		$Title = $this->Data->Title;
		$Subtitle = $this->Data->Subtitle;
		$URL = trim($this->Data->URL ?? $Title, '/');

		////////

		if(!$Title)
		$this->Quit(1, 'no Title specified');

		if(!$URL)
		$this->Quit(2, 'failed to generate URL for page');

		////////

		$Page = Atlantis\Page\Entity::Insert([
			'Title'    => $Title,
			'Subtitle' => $Subtitle,
			'Alias'    => $URL
		]);

		$Section = Atlantis\Page\Section::Insert([
			'PageID'   => $Page->ID,
			'Title'    => $Title,
			'Subtitle' => $Subtitle,
			'StyleBG'  => Atlantis\Page\Section::DefaultStyleBG(),
			'StylePad' => Atlantis\Page\Section::DefaultStylePad(),
		]);

		($this)
		->SetGoto("/{$Page->Alias}")
		->SetPayload($Page->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleDelete():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...));

		$Page = Atlantis\Page\Entity::GetByID($this->Data->ID);

		if(!$Page)
		$this->Quit(1, 'page ID not found');

		////////

		$Page->Drop();
		return;
	}


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/page/section')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleSectionGet():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...));

		////////

		$Sect = Atlantis\Page\Section::GetByID($this->Data->ID);

		if(!$Sect)
		$this->Quit(1, 'section ID not found');

		////////

		$this->SetPayload($Sect->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/section', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleSectionPost():
	void {

		($this->Data)
		->PageID(Common\Datafilters::TypeInt(...))
		->AfterID(Common\Datafilters::TypeInt(...))
		->StyleBG(Common\Datafilters::TypeStringNullable(...))
		->StylePad(Common\Datafilters::TypeStringNullable(...))
		->Type([
			Common\Datafilters::TypeStringNullable(...),
			(fn(Common\Struct\DatafilterItem $Val)=> $Val->Value ?? 'html')
		]);

		////////

		$Page = Atlantis\Page\Entity::GetByID($this->Data->PageID);

		if(!$Page)
		$this->Quit(1, 'no PageID found');

		////////

		$Dataset = [
			'PageID'   => $this->Data->PageID,
			'Type'     => $this->Data->Type,
			'StyleBG'  => $this->Data->StyleBG,
			'StylePad' => $this->Data->StylePad,
			'Sorting'  => 0
		];

		$Sorting = 1;
		$Sections = $Page->GetSections();

		foreach($Sections as $Sect) {
			/** @var Atlantis\Page\Section $Sect */

			$Sect->Update([ 'Sorting'=> $Sorting++ ]);

			if($Sect->ID === $this->Data->AfterID)
			$Dataset['Sorting'] = ($Sorting++);
		}

		if($Dataset['Sorting'] === 0)
		$Dataset['Sorting'] = $Sorting++;

		////////

		$Section = Atlantis\Page\Section::Insert($Dataset);

		////////

		$this->SetPayload($Section->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/section', Verb: 'PATCH')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleSectionPatch():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...))
		->Title(Common\Datafilters::TrimmedTextNullable(...))
		->Subtitle(Common\Datafilters::TrimmedTextNullable(...))
		->StyleBG(Common\Datafilters::TypeStringNullable(...))
		->StylePad(Common\Datafilters::TypeStringNullable(...))
		->Content(Common\Datafilters::TrimmedText(...));

		////////

		$Section = Atlantis\Page\Section::GetByID($this->Data->ID);

		if(!$Section)
		$this->Quit(1, 'no section ID found');

		////////

		$Section->Update([
			'Title'    => $this->Data->Title,
			'Subtitle' => $this->Data->Subtitle,
			'StyleBG'  => $this->Data->StyleBG,
			'StylePad' => $this->Data->StylePad,
			'Content'  => $this->Data->Content
		]);

		$this->SetPayload($Section->DescribeForPublicAPI());
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/section', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleSectionDelete():
	void {

		($this->Data)
		->ID(Common\Datafilters::TypeInt(...));

		$Section = Atlantis\Page\Section::GetByID($this->Data->ID);

		if(!$Section)
		$this->Quit(1, 'section ID not found');

		////////

		$Section->Drop();
		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/section', Verb: 'TYPES')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleSectionTypes():
	void {

		$this->SetPayload([
			'DefaultType' => 'HTML',
			'DefaultBG'   => 'Normal',
			'DefaultPad'  => 'Normal',
			'Type'        => Atlantis\Page\Section::$TypeList,
			'StyleBG'     => Atlantis\Page\Section::$StyleListBG,
			'StylePad'    => Atlantis\Page\Section::$StyleListPad
		]);

		return;
	}

}
