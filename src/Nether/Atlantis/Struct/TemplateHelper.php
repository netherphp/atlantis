<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;

class TemplateHelper {
/*//
@date 2023-06-07
//*/

	protected Atlantis\Engine
	$App;

	protected string
	$CacheBuster;

	public function
	__Construct(Atlantis\Engine $App) {

		$this->App = $App;
		$this->CacheBuster = md5(microtime(TRUE));

		return;
	}


	////////////////////////////////////////////////////////////////
	// methods that directly send output ///////////////////////////

	public function
	CacheBuster():
	void {

		echo $this->GetCacheBuster();
		return;
	}

	public function
	Print(?string $Input=NULL):
	void {
	/*//
	@date 2023-06-07
	//*/

		Atlantis\Util::PrintHTML($Input ?? '');
		return;
	}

	public function
	ThemeURL(string $In, ?string $Theme=NULL):
	void {
	/*//
	@date 2023-06-07
	//*/

		echo $this->GetThemeURL($In, $Theme);
		return;
	}

	////////////////////////////////////////////////////////////////
	// methods that return values //////////////////////////////////

	public function
	Encode(string $Input):
	string {
	/*//
	@date 2023-06-07
	//*/

		return Atlantis\Filter::EncodeHTML($Input);
	}

	public function
	GetCacheBuster():
	string {

		return $this->CacheBuster;
	}

	public function
	GetCheckedHTML(bool $Is):
	string {
	/*//
	@date 2023-06-07
	//*/

		return Atlantis\Util::GetCheckedHTML($Is);
	}

	public function
	GetSelectedHTML(bool $Is):
	string {
	/*//
	@date 2023-06-07
	//*/

		return Atlantis\Util::GetSelectedHTML($Is);
	}

	public function
	GetThemeURL(string $In, ?string $Theme=NULL):
	string {
	/*//
	@date 2023-06-07
	//*/

		$Theme ??= $this->App->Surface->GetTheme();

		return "/themes/{$Theme}/{$In}";
	}

}
