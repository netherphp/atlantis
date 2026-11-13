<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\UI;

use Nether\Captcha;
use Nether\Common;
use Nether\Common\Prototype\ConstructArgs;
use Nether\Surface;
use Override;

################################################################################
################################################################################

class FormCaptcha
extends Surface\Element {

	public string
	$Area = 'elements/captcha/main';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public Captcha
	$Captcha;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Override]
	public function
	OnReady(ConstructArgs $Args):
	void {

		parent::OnReady($Args);

		$this->Captcha = Captcha::Fresh();

		return;
	}

}
