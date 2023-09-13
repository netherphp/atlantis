<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

#[Common\Meta\Date('2023-06-07')]
class TemplateHelper {

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

	#[Common\Meta\Date('2023-09-12')]
	public function
	PrintJSON(mixed $Input):
	void {

		echo json_encode($Input);
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

	#[Common\Meta\Date('2023-08-07')]
	#[Common\Meta\Info('Mostly for rewriting URLs to our own application.')]
	public function
	RewriteURL(string $URL):
	string {

		$HTTPS = 'https://';

		if($this->App->IsDev())
		$HTTPS .= 'dev.';

		////////

		if(str_starts_with($URL, 'atl://www.'))
		$URL = str_replace('atl://', $HTTPS, $URL);

		elseif(str_starts_with($URL, 'atl://'))
		$URL = str_replace('atl://', $HTTPS, $URL);

		////////

		if(!str_starts_with($URL, 'http'))
		return (string)(new Atlantis\WebURL($URL));

		else
		return $URL;
	}

	public function
	Encode(string $Input):
	string {
	/*//
	@date 2023-06-07
	//*/

		return Atlantis\Filter::EncodeHTML($Input);
	}

	#[Common\Meta\Date('2023-09-12')]
	public function
	EncodeJSON(mixed $Input):
	string {

		return json_encode($Input);
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
