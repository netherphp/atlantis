<?php

namespace Nether\Atlantis\UI;

use Nether\Common;

class ToolbarButton
extends Element {

	public function
	Render():
	string {

		if(!str_contains($this->Class, 'btn-'))
		$this->Class .= ' btn-dark';

		////////

		$Output = sprintf('<button class="btn %s">', $this->Class);

		if($this->Icon)
		$Output .= $this->GetIconHTML();

		$Output .= $this->Title;
		$Output .= '</button>';

		////////

		return $Output;
	}

}
