<?php

namespace Nether\Atlantis;

use Stringable;
use Nether\Common\Datafilter;

class WebURL
implements Stringable {

	public ?string
	$Proto;

	public ?string
	$Host;

	public ?string
	$Path;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Path='/', ?string $Host=NULL, ?string $Proto=NULL) {

		if($Host === NULL)
		$Host = (
			Library::Get(Key::ConfProjectDomain)
			?? $_SERVER['HTTP_HOST']
		);

		$this->Set($Path, $Host, $Proto);
		return;
	}

	public function
	__ToString():
	string {

		return $this->Get();
	}

	public function
	ToJSON():
	string {

		return json_encode($this->Get());
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Get():
	string {

		$Output = sprintf(
			'%s://%s%s',
			($this->Proto ?? 'http'),
			($this->Host ?? ''),
			($this->Path ?? '')
		);

		return $Output;
	}

	public function
	Set(string $Path='/', string $Host=NULL, string $Proto=NULL):
	static {

		if(str_starts_with($Path, '/'))
		$this->SetByAbsolutePath($Path);

		elseif(preg_match('#^https?://#', $Path))
		$this->SetByURL($Path);

		else
		$this->SetByRelativePath($Path);

		////////

		// only overwrite if specified as one of the path support methods
		// is set by full url, and may have already filled those in.

		if($Host !== NULL)
		$this->Host = $Host;

		if($Proto !== NULL)
		$this->Proto = $Proto;

		////////

		// if we still have nothing then try to reasonable default.

		if(!isset($this->Host)) {
			if(isset($_SERVER['HTTP_HOST']))
			$this->Host = $_SERVER['HTTP_HOST'];
		}

		if(!isset($this->Proto)) {
			if(isset($_SERVER['HTTPS']))
			$this->Proto = 'https';
			else
			$this->Proto = 'http';
		}

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	SetByURL(string $URL):
	static {

		$Parsed = new Datafilter(parse_url($URL));

		// get the basics.
		$this->Proto = strtolower($Parsed->Scheme ?: 'http');
		$this->Host = ($Parsed->Host ? strtolower($Parsed->Host) : NULL);
		$this->Path = $Parsed->Path;

		// get the url query values.
		if($Parsed->Query)
		$this->Path .= "?{$Parsed->Query}";

		// get the url anchor fragments.
		if($Parsed->Fragment)
		$this->Path .= "#{$Parsed->Fragment}";

		return $this;
	}

	protected function
	SetByRelativePath(string $Path):
	static {

		if(isset($_SERVER['REQUEST_URI']))
		$this->Path = sprintf(
			'%s/%s',
			rtrim($_SERVER['REQUEST_URI'], '/'),
			$Path
		);

		else
		$this->Path = $Path;

		return $this;
	}

	protected function
	SetByAbsolutePath(string $Path):
	static {

		$this->Path = $Path;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Rewrite(Engine $App, string $URL):
	string {

		$HTTPS = 'https://';

		if($App->IsDev())
		$HTTPS .= 'dev.';

		////////

		if(str_starts_with($URL, 'atl://www.'))
		$URL = str_replace('atl://', $HTTPS, $URL);

		elseif(str_starts_with($URL, 'atl://'))
		$URL = str_replace('atl://', $HTTPS, $URL);

		////////

		if(!str_starts_with($URL, 'http'))
		return (string)(new static($URL));

		else
		return $URL;
	}

	static public function
	FromString(string $Input):
	static {

		return new static($Input);
	}

}
