<?php

namespace Nether\Atlantis\Struct\ProjectJSON;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class CertJSON
extends Common\Prototype
implements
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON,
	Common\Interfaces\ToString {

	use
	Common\Package\ToJSON,
	Common\Package\ToString;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	TypeAcmePHP = 'acmephp';

	const
	Types = [
		self::TypeAcmePHP
	];

	static public function
	Type(int|string $Key):
	int|string|NULL {

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
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$Domain = NULL;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Lists::class, 'ArrayOf' ])]
	#[Common\Meta\PropertyFactory('FromArray', 'AltDomains')]
	public array|Common\Datastore
	$AltDomains = [];

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$OrgName = NULL;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$OrgCountry = NULL;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$OrgCity = NULL;

	#[Common\Meta\PropertyListable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Email' ])]
	public ?string
	$TechEmail = NULL;

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common\Interfaces\ToArray ////////////////////////

	public function
	ToArray():
	array {

		return [
			'Type'       => $this->Type,
			'Sudo'       => $this->Sudo,
			'Domain'     => $this->Domain,
			'AltDomains' => $this->AltDomains->GetData(),
			'OrgName'    => $this->OrgName,
			'OrgCountry' => $this->OrgCountry,
			'OrgCity'    => $this->OrgCity,
			'TechEmail'  => $this->TechEmail
		];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ToAcmeYaml(Atlantis\Engine $App, string $CertRoot='/opt/ssl'):
	string {

		$WebRoot = $App->GetWebRoot();

		// generate a merged list of values.

		$Values = Common\Datastore::FromStackMerged($this->ToArray(), [
			'WebRoot'  => $WebRoot,
			'CertRoot' => $CertRoot
		]);

		// try to load the template file.

		$File = Common\Filesystem\Util::Pathify(
			$App->FromProjectRoot(),
			'vendor', 'netherphp', 'atlantis',
			'templates', 'acmephp.txt'
		);

		$Template = Common\TemplateFile::FromFile($File);

		// bake the values as needed before shipping them out.

		$Values->RemapKeys(function(string $K, mixed $V, Common\Datastore $D) {
			switch($K) {
				case 'AltDomains': {
					$V = trim(
						Common\Datastore::FromArray($V)
						->Unshift($D['Domain'])
						->Accumulate('', fn(string $P, string $N)=> sprintf(
							'%s      - \'%s\'%s',
							$P,
							Common\Filters\Text::YamlEscapeSingleQuote($N),
							PHP_EOL
						))
					);
					break;
				}

				default: {
					$V = trim(Common\Filters\Text::YamlEscapeSingleQuote($V));
					break;
				}
			}

			return [ $K=> $V ];
		});

		return $Template->ReplaceTokensWith($Values);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasAnything():
	bool {

		if(isset($this->Type) && isset($this->Domain))
		return TRUE;

		return FALSE;
	}

};
