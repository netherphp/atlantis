<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\Email;
use Nether\Storage;

class IndexWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	MainGet():
	void {

		$this->Surface
		->Set('Page.Title', 'Admin')
		->Wrap('admin/index', [
			'EmailConfigInfo'   => new Email\Struct\LibraryConfigInfo,
			'StorageConfigInfo' => new Storage\Struct\LibraryConfigInfo
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/ops/api')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	ApiGet():
	void {

		$Index = (
			($this->App->Router->GetHandlers())
			->Map(fn($V)=> $V->Map(fn($W)=> $W->Path))
		);

		$Verbs = (
			(new Common\Datastore($Index->Keys()))
			->Sort()
		);

		$Endpoints = (
			$Index
			->Accumulate(
				new Common\Datastore([]),
				fn($I, $V)=> $I->MergeRight($V)
			)
			->Unique()
			->Sort()
		);

		($this->Surface)
		->Set('Page.Title', 'API Tool')
		->Wrap('admin/apitool/index', [
			'Index'     => $Index,
			'Verbs'     => $Verbs,
			'Endpoints' => $Endpoints
		]);

		return;
	}

}
