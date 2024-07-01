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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleGet():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandlePost():
	void {

		($this->Data)
		->Title(Common\Filters\Text::Trimmed(...))
		->Subtitle(Common\Filters\Text::TrimmedNullable(...))
		->URL(Common\Filters\Text::PathableKey(...));

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
			'StylePad' => Atlantis\Page\Section::DefaultStylePad()
		]);

		($this)
		->SetGoto("/{$Page->Alias}")
		->SetPayload($Page->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/entity', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleDelete():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleSectionGet():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleSectionPost():
	void {

		($this->Data)
		->PageID(Common\Filters\Numbers::IntType(...))
		->AfterID(Common\Filters\Numbers::IntType(...))
		->Title(Common\Filters\Text::Trimmed(...))
		->Subtitle(Common\Filters\Text::Trimmed(...))
		->StyleBG(Common\Filters\Text::StringNullable(...))
		->StylePad(Common\Filters\Text::StringNullable(...))
		->Type([
			Common\Filters\Text::StringNullable(...),
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
			'Title'    => $this->Data->Title,
			'Subtitle' => $this->Data->Subtitle,
			'StyleBG'  => $this->Data->StyleBG,
			'StylePad' => $this->Data->StylePad,
			'Sorting'  => 0
		];

		$Sorting = 1;
		$Sections = $Page->GetSections();
		$Sect = NULL;

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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleSectionPatch():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->Title(Common\Filters\Text::TrimmedNullable(...))
		->Subtitle(Common\Filters\Text::TrimmedNullable(...))
		->StyleBG(Common\Filters\Text::TrimmedNullable(...))
		->StylePad(Common\Filters\Text::TrimmedNullable(...))
		->Content(Common\Filters\Text::Trimmed(...));

		////////

		$Section = Atlantis\Page\Section::GetByID($this->Data->ID);

		if(!$Section)
		$this->Quit(1, 'no section ID found');

		$Page = Atlantis\Page\Entity::GetByID($Section->PageID);

		if(!$Page)
		$this->Quit(2, 'page not found');

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
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleSectionDelete():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...));

		$Section = Atlantis\Page\Section::GetByID($this->Data->ID);

		if(!$Section)
		$this->Quit(1, 'section ID not found');

		$Page = Atlantis\Page\Entity::GetByID($Section->PageID);

		if(!$Page)
		$this->Quit(2, 'page not found');

		////////

		$Section->Drop();

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/section', Verb: 'MOVE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleSectionSortingMove():
	void {

		($this->Data)
		->ID(Common\Filters\Numbers::IntType(...))
		->Move(Common\Filters\Numbers::IntType(...));

		////////

		$Section = Atlantis\Page\Section::GetByID($this->Data->ID);

		if(!$Section)
		$this->Quit(1, 'no section by ID found');

		$Page = Atlantis\Page\Entity::GetByID($Section->PageID);

		if(!$Page)
		$this->Quit(2, 'page not found');

		////////

		$Sections = $Page->GetSections();

		$Sections
		->Each(function(Atlantis\Page\Section $S) {
			// update the sorting value.

			if($S->ID === $this->Data->ID)
			$S->Sorting += $this->Data->Move;

			return;
		})
		->SortKeys(function(int $A, int $B) use($Sections) {
			// sort by the index unless the sorting value is the same
			// in which case flip them.

			if($Sections[$A]->Sorting === $Sections[$B]->Sorting)
			return $B <=> $A;

			return $A <=> $B;
		})
		->Reindex()
		->Each(function(Atlantis\Page\Section $S, int $K) {
			$S->Update([ 'Sorting' => ($K + 1) ]);
			return;
		});

		return;
	}

	#[Atlantis\Meta\RouteHandler('/api/page/section', Verb: 'TYPES')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
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
