<?php

namespace Nether\Atlantis\Struct\ProjectJSON;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////\

class WebJSON
extends Common\Prototype
implements
	Common\Interfaces\IsConfigured,
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON,
	Common\Interfaces\ToString {

	use
	Common\Package\ToJSON,
	Common\Package\ToString;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	TypeApacheCtl = 'apachectl';

	const
	Types = [
		self::TypeApacheCtl
	];

	static public function
	Type(int|string $Key):
	?string {

		if(is_string($Key)) {
			$Key = array_search($Key, static::Types, TRUE);

			if($Key === FALSE)
			return NULL;

			return (int)$Key;
		}

		if(!isset(static::Types[$Key]))
		return NULL;

		return static::Types[$Key];
	}

	static public function
	TypeIsValid(int|string $Key):
	bool {

		if(is_string($Key)) {
			$Key = array_search($Key, static::Types, TRUE);
			return ($Key !== FALSE);
		}

		return array_key_exists($Key, static::Types);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$Type = NULL;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'BoolType' ])]
	public bool
	$Sudo = TRUE;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'BoolType' ])]
	public bool
	$HTTPS = FALSE;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Lists::class, 'ArrayOf' ])]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Datastore
	$Domains = [ ];

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common Interfaces ////////////////////////////////

	public function
	IsType(int|string $Type):
	bool {

		if(is_string($Type))
		return $this->Type === $Type;

		return $this->Type === static::Type($Type);
	}

	public function
	ToArray():
	array {

		return [
			'Type'    => $this->Type,
			'Sudo'    => $this->Sudo,
			'HTTPS'   => $this->HTTPS,
			'Domains' => $this->Domains->GetData()
		];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ToApache24(Atlantis\Engine $App):
	string {

		$Uses = new Common\Datastore;
		$Output = NULL;

		$TemplateFile = $App->FromProjectRoot(Common\Filesystem\Util::Pathify(
			'vendor', 'netherphp', 'atlantis',
			'templates', 'apache24.txt'
		));

		////////

		if(!file_exists($TemplateFile))
		throw new Common\Error\FileNotFound($TemplateFile);

		($this->Domains)
		->Each(function(string $Line) use($App, $Uses) {
			$Bits = explode(' ', $Line);
			$Bit = NULL;
			$Dom = current($Bits);

			// Use $Macro $Domain $SSLDomain $WebRoot

			foreach($Bits as $Bit)
			$Uses->Push(sprintf(
				'Use %s %s %s %s',
				($this->HTTPS ? 'HTTPS' : 'HTTP'),
				$Bit,
				$Dom,
				$App->GetWebRoot()
			));

			return;
		});

		////////

		$Output = file_get_contents($TemplateFile);

		Common\Datastore::FromArray([
			'DOMAINS' => $Uses->Join(PHP_EOL)
		])
		->Each(function(string $V, string $K) use(&$Output) {
			$Output = str_replace("{%{$K}%}", $V, $Output);
			return;
		});

		////////

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsConfigured():
	bool {

		return (TRUE
			&& ($this->Type !== NULL)
			&& ($this->Domains->Count() > 0)
		);
	}

	public function
	HasAnything():
	bool {

		if($this->Type)
		return TRUE;

		return FALSE;
	}

};
