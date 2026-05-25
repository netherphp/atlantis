<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class ProfilesWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/profiles')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	Index():
	void {

		$Query = Common\Filters\Text::TrimmedNullable($this->Data->Get('q'));
		$UUID = Common\Filters\Text::TrimmedNullable($this->Data->Get('uuid'));
		$Enabled = Common\Filters\Numbers::IntNullable($this->Data->Get('enabled'));
		$Page = Common\Filters\Numbers::Page($this->Data->Get('page'));
		$Limit = Common\Filters\Numbers::IntRange($this->Data->Get('limit'), 0, 100) ?: 25;

		////////

		$Tags = Common\Filters\Text::TrimmedNullable($this->Data->Get('tags'));

		$Tags = (
			(Common\Datastore::FromString($Tags, '-'))
			->Remap(Common\Filters\Numbers::IntType(...))
			->Remap(Atlantis\Tag\Entity::GetByID(...))
			->Filter(fn(?Atlantis\Tag\Entity $T)=> $T !== NULL)
		);

		////////

		$TagsAll = NULL;

		if($Tags->Count())
		$TagsAll = $Tags->Map(fn(Atlantis\Tag\Entity $T)=> $T->ID)->Export();

		////////

		$Profiles = Atlantis\Profile\Entity::Find([
			'UUID'        => $UUID,
			'Search'      => $Query,
			'SearchTitle' => TRUE,
			'TagsAll'     => $TagsAll,
			'Enabled'     => $Enabled,
			'Page'        => $Page,
			'Limit'       => $Limit,
			'Sort'        => 'title-az'
		]);

		////////

		$this->Area('admin/profiles/index', [
			'Profiles' => $Profiles,
			'Tags'     => $Tags,
			'Query'    => $Query
		]);

		return;
	}

};
