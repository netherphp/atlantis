<?php

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Avenue;

class VideoWeb
extends Atlantis\ProtectedWeb {

	#[Avenue\Meta\RouteHandler('/video/:VideoID:')]
	#[Avenue\Meta\ConfirmWillAnswerRequest]
	public function
	View(int $VideoID, Atlantis\Media\VideoThirdParty $Video):
	void {

		$this->Surface
		->Wrap('media/video/view', [
			'Video' => $Video
		]);

		return;
	}

	public function
	ViewWillAnswerRequest(int $VideoID, Avenue\Struct\ExtraData $Data):
	int {

		$Video = Atlantis\Media\VideoThirdParty::GetBYID($VideoID);

		if(!$Video)
		return ($this->Response)::CodeNope;

		////////

		$Data['Video'] = $Video;

		return ($this->Response)::CodeOK;
	}

}
