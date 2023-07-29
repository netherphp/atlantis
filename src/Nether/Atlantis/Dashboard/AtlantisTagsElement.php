<?php

namespace Nether\Atlantis\Dashboard;

use Nether\Atlantis;

class AtlantisTagsElement
extends Atlantis\Dashboard\Element {

	public int
	$TagCount = 0;

	public int
	$PhotoCount = 0;

	public int
	$VideoCount = 0;

	public int
	$VideoThirdPartyCount = 0;

	public int
	$ProfileCount = 0;

	public function
	__Construct(Atlantis\Engine $App) {

		parent::__Construct(
			$App,
			'CMS',
			'atlantis/dashboard/tag/element-dash'
		);

		$this->TagCount = Atlantis\Tag\Entity::FindCount([]);
		$this->PhotoCount = Atlantis\Media\File::FindCount([ 'Type'=> 'img' ]);
		$this->VideoCount = 0;
		$this->VideoThirdPartyCount = Atlantis\Media\VideoThirdParty::FindCount([]);
		$this->ProfileCount = Atlantis\Profile\Entity::FindCount([]);

		return;
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENT Atlantis\Dashbord\Element /////////////////////////

	protected function
	OnReady():
	void {

		$this->Columns = 'half';

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetTagCount():
	int {

		return $this->TagCount;
	}

	public function
	GetPhotoCount():
	int {

		return $this->PhotoCount;
	}

	public function
	GetVideoCount(bool $Total=TRUE):
	int {

		if($Total)
		return $this->VideoCount + $this->VideoThirdPartyCount;

		return $this->VideoCount;
	}

	public function
	GetVideoThirdPartyCount():
	int {

		return $this->VideoThirdPartyCount;
	}

	public function
	GetProfileCount():
	int {

		return $this->ProfileCount;
	}

}
