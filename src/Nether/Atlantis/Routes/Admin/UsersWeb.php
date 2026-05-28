<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\User;

class UsersWeb
extends Atlantis\ProtectedWeb {

	use Atlantis\Packages\RouteInvokeForData;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/ops/users')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	Index():
	void {

		$Query = Common\Filters\Text::TrimmedNullable($this('q'));

		////////

		$Users = User\Entity::Find([
			'Search'      => $Query,
			'SearchAlias' => TRUE,
			'SearchEmail' => TRUE,

			'Page'        => 1,
			'Limit'       => 25,
			'Sort'        => 'newest'
		]);

		$Defined = Atlantis\User\AccessTypeList::Fetch($this->App);

		($this)
		->SetPageTitle('Users // Operations')
		->Area('admin/users/index', [
			'Users' => $Users,
			'Query' => $Query,
			'DefinedAccessTypes' => $Defined
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/ops/users/:ID:')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	View(string $ID):
	void {

		$Entity = User\EntitySession::GetByID($ID);
		$Access = $Entity->GetAccessTypes();
		$Defined = Atlantis\User\AccessTypeList::Fetch($this->App);

		if(!$Entity)
		$this->Quit(404);

		$Auths = new Common\Datastore([
			'Apple'    => (!!$Entity->AuthAppleID),
			'Github'   => (!!$Entity->AuthGitHubID),
			'Discord'  => (!!$Entity->AuthDiscordID),
			'Google'   => (!!$Entity->AuthGoogleID),
			'Password' => (!!$Entity->PHash),
		]);

		($this)
		->SetPageTitle(sprintf('%s // Users // Operations', $Entity->Email))
		->Area('admin/users/view', [
			'Who'                => $Entity,
			'Access'             => $Access,
			'AuthList'           => $Auths,
			'DefinedAccessTypes' => $Defined
		]);

		return;
	}

}
