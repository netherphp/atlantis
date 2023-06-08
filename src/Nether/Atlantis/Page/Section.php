<?php

namespace Nether\Atlantis\Page;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('PageSections', 'PS')]
class Section
extends Atlantis\Prototype {

	const
	TypeHTML = 'html';

	static public array
	$TypeList = [
		'HTML' => 'html'
	];

	static public array
	$StyleListBG = [
		'Normal' => 'bg-alt0',
		'Alt1'   => 'bg-alt1',
		'Alt2'   => 'bg-alt2'
	];

	static public array
	$StyleListPad = [
		'None'   => 'pt-0 pb-0',
		'Less'   => 'pt-4 pb-4',
		'Normal' => 'pt-6 pb-6',
		'Max'    => 'pt-8 pb-8'
	];

	static public function
	DefaultType():
	string {

		return static::$TypeList['HTML'];
	}

	static public function
	DefaultStyleBG():
	string {

		return static::$StyleListBG['Normal'];
	}

	static public function
	DefaultStylePad():
	string {

		return static::$StyleListPad['Normal'];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Pages', 'ID', Delete: TRUE)]
	public int
	$PageID;

	#[Database\Meta\TypeVarChar(Size: 64, Default: NULL)]
	public ?string
	$Title;

	#[Database\Meta\TypeVarChar(Size: 64, Default: NULL)]
	public ?string
	$Subtitle;

	#[Database\Meta\TypeVarChar(Size: 16, Default: 'html')]
	public string
	$Type;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1)]
	public int
	$Sorting;

	#[Database\Meta\TypeIntTiny(Unsigned: TRUE, Default: 1)]
	public bool
	$Contain;

	#[Database\Meta\TypeVarChar(Size: 128, Default: NULL)]
	public ?string
	$StyleBG;

	#[Database\Meta\TypeVarChar(Size: 128, Default: NULL)]
	public ?string
	$StylePad;

	#[Database\Meta\TypeText]
	public ?string
	$Content;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Render(Atlantis\Engine $App, Atlantis\Page\Entity $Page):
	string {

		$Output = '';

		if($this->Type === static::TypeHTML) {
			$Output .= $App->Surface->GetArea('page/section-html', [
				'Page'    => $Page,
				'Section' => $this
			]);
		}

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	static {

		$Data = new Common\Datastore($Input);
		$Data->BlendRight([
			'UUID' => Common\UUID::V7()
		]);

		return parent::Insert($Data);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['Sort'] ??= 'title-az';
		$Input['PageID'] ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['PageID'] !== NULL)
		$SQL->Where('Main.PageID=:PageID');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			default: {
				$SQL->Sort('Main.Sorting', $SQL::SortAsc);
				break;
			}
		}

		return;
	}

}
