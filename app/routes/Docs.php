<?php

namespace Routes;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class Docs
extends Atlantis\PublicWeb {

	public string
	$Title = 'Documentation';

	public string
	$Info = '';

	public Atlantis\UI\Pathbar
	$Pathbar;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnReady(?Common\Datastore $ExtraData):
	void {

		parent::OnReady($ExtraData);

		$this->Pathbar = Atlantis\UI\Pathbar::FromSurface(
			$this->Surface
		);

		($this->Pathbar->Items)
		->Push(Atlantis\Struct\Item::New(
			Title: 'Docs', URL: '/docs',
			Classes: [ 'tag' ]
		));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Avenue\Meta\RouteHandler('/docs')]
	public function
	Index():
	void {

		($this->Surface)
		->Set('Page.Title', $this->Title)
		->Area('sensei/docs/index');

		return;
	}

	#[Avenue\Meta\RouteHandler('/docs/:Page:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	Section(string $Page, string $Area):
	void {

		$Content = $this->Surface->GetArea($Area);

		////////

		($this->Surface)
		->Set('Page.Title', sprintf(
			'%s - Documentation',
			$this->Title
		));

		echo $Content;

		return;
	}

	protected function
	SectionWillAnswerRequest(string $Page, Avenue\Struct\ExtraData $Data):
	int {

		$Area = sprintf(
			'sensei/docs/%s',
			Common\Filters\Text::SlottableKey($Page)
		);

		$File = Common\Filesystem\Util::Pathify(
			$this->App->GetProjectRoot(),
			'www', 'themes', 'default', 'area',
			"{$Area}.phtml"
		);

		if(!file_exists($File))
		return Avenue\Response::CodeNope;

		////////

		$Data['Area'] = $Area;

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Avenue\Meta\RouteHandler('/docs/ui/:Page:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	SectionElementPage(string $Page, string $Area):
	void {

		($this->Pathbar->Items)
		->Push(Atlantis\Struct\Item::New(
			Title: 'Element UI',
			URL: '/docs/ui'
		));

		$Content = $this->Surface->GetArea($Area);

		////////

		($this->Surface)
		->Set('Page.Title', sprintf(
			'%s - Element UI - Documentation',
			$this->Title
		))
		->Area('sensei/docs/__header', [
			'Title'   => $this->Title,
			'Section' => $this->Pathbar,
			'Info'    => $this->Info
		]);

		echo $Content;

		return;
	}

	protected function
	SectionElementPageWillAnswerRequest(string $Page, Avenue\Struct\ExtraData $Data):
	int {

		$Area = sprintf(
			'sensei/docs/ui/%s/index',
			Common\Filters\Text::SlottableKey($Page)
		);

		$File = Common\Filesystem\Util::Pathify(
			$this->App->GetProjectRoot(),
			'www', 'themes', 'default', 'area',
			"{$Area}.phtml"
		);

		////////

		if(!file_exists($File))
		return Avenue\Response::CodeNope;

		////////

		$Data['Area'] = $Area;

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Avenue\Meta\RouteHandler('/docs/css/:Page:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	SectionStylesPage(string $Page, string $Area):
	void {

		($this->Pathbar->Items)
		->Push(Atlantis\Struct\Item::New(
			Title: 'CSS Reference',
			URL: '/docs/css'
		));

		$Content = $this->Surface->GetArea($Area);

		////////

		($this->Surface)
		->Set('Page.Title', sprintf(
			'%s - CSS - Documentation',
			$this->Title
		))
		->Area('sensei/docs/__header', [
			'Title'   => $this->Title,
			'Section' => $this->Pathbar,
			'Info'    => $this->Info
		]);

		echo $Content;

		return;
	}

	protected function
	SectionStylesPageWillAnswerRequest(string $Page, Avenue\Struct\ExtraData $Data):
	int {

		$Area = sprintf(
			'sensei/docs/css/%s/index',
			Common\Filters\Text::SlottableKey($Page)
		);

		$File = Common\Filesystem\Util::Pathify(
			$this->App->GetProjectRoot(),
			'www', 'themes', 'default', 'area',
			"{$Area}.phtml"
		);

		////////

		if(!file_exists($File))
		return Avenue\Response::CodeNope;

		////////

		$Data['Area'] = $Area;

		return Avenue\Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Avenue\Meta\RouteHandler('/docs/cli/:Page:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	CommandsStylesPage(string $Page, string $Area):
	void {

		($this->Pathbar->Items)
		->Push(Atlantis\Struct\Item::New(
			Title: 'CLI Reference',
			URL: '/docs/cli'
		));

		$Content = $this->Surface->GetArea($Area, [
			'Page' => $Page
		]);

		////////

		($this->Surface)
		->Set('Page.Title', sprintf(
			'%s - CLI - Documentation',
			$this->Title
		))
		->Area('sensei/docs/__header', [
			'Title'   => $this->Title,
			'Section' => $this->Pathbar,
			'Info'    => $this->Info
		]);

		echo $Content;

		return;
	}

	protected function
	CommandsStylesPageWillAnswerRequest(string $Page, Avenue\Struct\ExtraData $Data):
	int {

		$Area = sprintf(
			'sensei/docs/cli/%s/index',
			Common\Filters\Text::SlottableKey($Page)
		);

		$File = Common\Filesystem\Util::Pathify(
			$this->App->GetProjectRoot(),
			'www', 'themes', 'default', 'area',
			"{$Area}.phtml"
		);

		////////

		if(!file_exists($File))
		return Avenue\Response::CodeNope;

		////////

		$Data['Area'] = $Area;

		return Avenue\Response::CodeOK;
	}

};
