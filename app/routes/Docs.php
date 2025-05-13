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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	FetchHeading(string $Title, iterable $Trail=[]):
	string {

		$Output = $this->Surface->GetArea('sensei/docs/__section', [
			'Title' => $Title,
			'Trail' => $Trail
		]);

		return $Output;
	}

	public function
	FetchExamplePHP(string $Filename):
	string {

		$Output = $this->Surface->GetArea('sensei/docs/__example-via-php', [
			'Filename' => $Filename
		]);

		return $Output;
	}


};
