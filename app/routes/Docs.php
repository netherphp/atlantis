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

		$PTitle = 'Documentation';
		$Pathbar = Atlantis\UI\Pathbar::FromSurface($this->Surface);

		////////

		($Pathbar->Items)
		->Push(Atlantis\Struct\Item::New(
			Title: 'Docs', URL: '/docs',
			Classes: [ 'tag' ]
		));

		($this->Surface)
		->Set('Page.Title', $PTitle)
		->Area('sensei/docs/__header', [
			'Section' => $Pathbar,
			'Title'   => 'Documentation'
		])
		->Area('sensei/docs/index');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Avenue\Meta\RouteHandler('/docs/:Sect:')]
	#[Avenue\Meta\RouteHandler('/docs/:Sect:/:Page:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	SectionNew(?string $Sect=NULL, ?string $Page=NULL, ?string $Area=NULL):
	void {

		$STitle = NULL;
		$PTitle = NULL;
		$SLink = sprintf('/docs/%s', $Sect);
		$PLink = sprintf('/docs/%s/%s', $Sect, $Page);
		$Pathbar = Atlantis\UI\Pathbar::FromSurface($this->Surface);

		// allow the doc pages to set their page meta in their templates
		// that we apply consistently from out here.

		$Content = $this->Surface->GetArea($Area, [
			'Section' => $Sect,
			'Page'    => $Page
		]);

		$PTitle = $this->Surface->Get('Page.Title');

		$STitle = $this->Surface->Get('Page.Section.Title');
		$SLink = $this->Surface->Get('Page.Section.Link') ?? "/docs/{$Sect}";
		$PInfo = $this->Surface->Get('Page.Info');

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
			'Section' => $Pathbar,
			'Title'   => $PTitle,
			'Info'    => $PInfo
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

};
