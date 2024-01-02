<?php

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

class AceEditor
extends Surface\Element {

	public string
	$Area = 'elements/ace-editor/main';

	public string
	$Content = '';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromSurfaceWithContent(Surface\Engine $Surface, ?string $Content=NULL):
	static {

		return static::FromSurfaceWith($Surface, [
			'Content' => ($Content ?? '')
		]);
	}

};
