<?php

namespace Routes;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Surface;

class Docs
extends Atlantis\PublicWeb {

	#[Avenue\Meta\RouteHandler('/docs')]
	public function
	Index():
	void {

		$Scope = [];

		////////

		($this->Surface)
		->Area('sensei/docs/index', $Scope);

		return;
	}

	#[Avenue\Meta\RouteHandler('/docs/::Path::')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	#[Avenue\Meta\ExtraDataArgs]
	public function
	Section(string $Path, Avenue\Struct\ExtraData $ExtraData):
	void {

		$Scope = [];

		////////

		($this->Surface)
		->Area($ExtraData['Area'], $Scope);

		return;
	}

	public function
	SectionWillAnswerRequest(string $Path, ?Avenue\Struct\ExtraData $ExtraData):
	int {

		$Area = sprintf('sensei/docs/%s', $Path);

		////////

		if(!$this->Surface->HasArea($Area))
		$Area = sprintf('%s/index', $Area);

		if(!$this->Surface->HasArea($Area))
		return Avenue\Response::CodeNope;

		////////

		$ExtraData['Area'] = $Area;

		return Avenue\Response::CodeOK;
	}

	/*
	#[Avenue\Meta\RouteHandler('/docs')]
	public function
	Index():
	void {

		$PTitle = 'Framework Overview';
		$Pathbar = Atlantis\UI\Pathbar::FromSurface($this->Surface);

		////////

		($Pathbar->Items)
		->Push(Atlantis\Struct\Item::New(
			Title: 'Docs', URL: '/docs', Classes: [ 'tag' ]
		));

		////////

		($this->Surface)
		->Set('Page.Title', $PTitle)
		->Area(
			'sensei/docs/__header',
			[ 'Section'=> $Pathbar, 'Title'=> $PTitle ]
		)
		->Area('sensei/docs/index');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Avenue\Meta\RouteHandler('/docs/:Sect:')]
	#[Avenue\Meta\RouteHandler('/docs/:Sect:/:Page:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	#[Avenue\Meta\ExtraDataArgs]
	public function
	SectionNew(?string $Sect=NULL, ?string $Page=NULL, ?string $Area=NULL):
	void {

		$Pathbar = Atlantis\UI\Pathbar::FromSurface($this->Surface);

		$Content = $this->Surface->GetArea($Area, [
			'Section' => $Sect,
			'Page'    => $Page
		]);

		$PTitle = $this->Surface->Get('Page.Title');
		$PInfo = $this->Surface->Get('Page.Info');
		$PLink = sprintf('/docs/%s/%s', $Sect, $Page);

		$STitle = $this->Surface->Get('Page.Section.Title');
		$SInfo = $this->Surface->Get('Page.Section.Info');
		$SLink = $this->Surface->Get('Page.Section.Link') ?? "/docs/{$Sect}";

		////////

		($Pathbar->Items)
		->Push(Atlantis\Struct\Item::New(
			Title: 'Docs', URL: '/docs', Classes: [ 'tag' ]
		));

		if($STitle)
		$Pathbar->Items->Push(Atlantis\Struct\Item::New(
			Title: $STitle, URL: $SLink
		));

		////////

		$Header = $this->Surface->GetArea('sensei/docs/__header', [
			'Section'  => $Pathbar,
			'Title'    => $PTitle,
			'Info'     => $PInfo,
			'TitleURL' => $PLink
		]);

		////////

		echo $Header;
		echo $Content;

		return;
	}

	protected function
	SectionNewWillAnswerRequest(?string $Sect=NULL, ?string $Page=NULL, ?Avenue\Struct\ExtraData $Data=NULL):
	int {

		$Area = NULL;
		$Path = NULL;

		////////

		$Area = match(TRUE) {
			($Sect && $Page)
			=> sprintf('sensei/docs/%s/%s/index', $Sect, $Page),

			($Sect && !$Page)
			=> sprintf('sensei/docs/%s/index', $Sect),

			default
			=> NULL
		};

		if($Area === NULL)
		return Avenue\Response::CodeNotFound;

		////////

		$Path = $this->Surface->FindAreaFile($Area);

		if($Path === NULL)
		return Avenue\Response::CodeNotFound;

		////////

		$Data['Area'] = $Area;
		$Data['Path'] = $Path;

		return Avenue\Response::CodeOK;
	}
	*/

};
