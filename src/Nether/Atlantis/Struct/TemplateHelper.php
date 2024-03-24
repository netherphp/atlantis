<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

use Exception;

#[Common\Meta\Date('2023-06-07')]
class TemplateHelper {

	protected Atlantis\Engine
	$App;

	protected string
	$CacheBuster;

	public function
	__Construct(Atlantis\Engine $App) {

		$this->App = $App;

		$this->CacheBuster = match(TRUE) {

			(file_exists($App->FromProjectRoot('data/cache.bust')))
			=> htmlentities(strip_tags(
				file_get_contents($App->FromProjectRoot('data/cache.bust'))
			)),

			($App->IsDev())
			=> htmlentities(strip_tags(
				Common\UUID::V7()
			)),

			default
			=> 'static'
		};

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

		if($Input === NULL)
		return;

		// this is some magic that will need to be documented.

		if(str_starts_with($Input, 'atl://'))
		$Input = Atlantis\WebURL::Rewrite($this->App, $Input);

		////////

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

	#[Common\Meta\Date('2023-10-18')]
	public function
	ValueFrom(string $What, ...$Argv):
	mixed {

		$Arg = NULL;

		foreach($Argv as $Arg) {

			if(is_array($Arg)) {
				if(array_key_exists($What, $Arg))
				return $Arg[$What];
			}

			elseif($Arg instanceof Common\Datastore) {
				if($Arg->HasKey($What))
				return $Arg->Get($What);
			}

			elseif(is_object($Arg)) {
				if(property_exists($Arg, $What))
				return $Arg->{$What};
			}

		}

		return '';
	}

	#[Common\Meta\Date('2023-08-07')]
	#[Common\Meta\Info('Returns the URL after running it through the system thing.')]
	public function
	RewriteURL(string $URL):
	string {

		return Atlantis\WebURL::Rewrite($this->App, $URL);
	}

	#[Common\Meta\Date('2023-08-07')]
	#[Common\Meta\Info('Prints the URL running it through the system thing.')]
	public function
	PrintURL(string $URL):
	string {

		return $this->RewriteURL($URL);
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
