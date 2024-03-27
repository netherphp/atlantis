<?php

namespace Nether\Atlantis\Routes\Dashboard;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

class ProfileDashboardWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/dashboard/profiles')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	ListGet():
	void {

		($this->Data)
		->Q(Common\Filters\Text::TrimmedNullable(...))
		->Untagged(Common\Filters\Numbers::BoolNullable(...))
		->Page(Common\Filters\Numbers::Page(...))
		->SiteTag(Common\Filters\Text::TrimmedNullable(...))
		->Sort(Common\Filters\Misc::OneOfTheseFirst(...), [ 'title-az', 'title-za' ]);

		$Trail = new Common\Datastore([
			'Profiles' => '/dashboard/profiles'
		]);

		////////

		$SiteTags = Atlantis\Tag\Entity::Find([
			'Type' => 'site'
		]);

		$SiteTag = NULL;

		if($this->Data->SiteTag)
		$SiteTag = Atlantis\Tag\Entity::GetByField('Alias', $this->Data->SiteTag);

		$Filters = [
			'Enabled'  => FALSE,
			'UseSiteTags' => FALSE,
			'Search'   => $this->Data->Q,
			'Untagged' => $this->Data->Untagged,
			'Page'     => $this->Data->Page,
			'Sort'     => $this->Data->Sort,
			'Limit'    => 20
		];

		if($SiteTag)
		$Filters['TagID'] = $SiteTag->ID;

		$Profiles = Atlantis\Profile\Entity::Find($Filters);
		$Profiles->Each(fn(Atlantis\Profile\Entity $P)=> $P->GetTags());

		$Searched = (FALSE
			|| $this->Data->Q
			|| $this->Data->Untagged
		);



		////////

		($this->Surface)
		->Set('Page.Title', 'Manage Profiles - Dashboard')
		->Wrap('atlantis/dashboard/profile/index', [
			'Trail'    => $Trail,
			'SiteTags' => $SiteTags,
			'SiteTag'  => $SiteTag,
			'Filters'  => $Filters,
			'Searched' => $Searched,
			'Profiles' => $Profiles
		]);

		return;
	}

}
