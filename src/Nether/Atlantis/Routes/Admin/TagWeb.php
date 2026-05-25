<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;

################################################################################
################################################################################

class TagWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/tags')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	Index():
	void {

		$Query = Common\Filters\Text::TrimmedNullable($this->Data->Get('q'));
		$UUID = Common\Filters\Text::TrimmedNullable($this->Data->Get('uuid'));
		$Page = Common\Filters\Numbers::Page($this->Data->Get('page'));
		$Limit = Common\Filters\Numbers::IntRange($this->Data->Get('limit'), 0, 100) ?: 25;

		$Tags = Atlantis\Tag\Entity::Find([
			'UUID'       => $UUID,
			'Search'     => $Query,
			'SearchName' => TRUE,
			'Page'       => $Page,
			'Limit'      => $Limit,
			'Sort'       => 'name-az'
		]);

		////////

		($this)
		->SetPageTitle('Tags // Operations')
		->Area('admin/tags/index', [
			'Query' => $Query,
			'Tags'  => $Tags
		]);

		return;
	}

};
