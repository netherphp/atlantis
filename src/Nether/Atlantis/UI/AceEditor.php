<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\UI;

use Nether\Surface;

################################################################################
################################################################################

class AceEditor
extends Surface\Element {

	public string
	$Area = 'elements/ace-editor/main';

	public string
	$Lang = 'text';

	public string
	$Content = '';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromSurfaceWithContent(Surface\Engine $Surface, ?string $Content=NULL, ?string $Lang=NULL):
	static {

		return static::FromSurfaceWith($Surface, [
			'Lang'    => ($Lang ?? 'text'),
			'Content' => ($Content ?? '')
		]);
	}

};
