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
	TypeAcmePHP = 'acmephp',
	TypeAcmeSH  = 'acmesh';

	const
	Types = [
		self::TypeAcmePHP,
		self::TypeAcmeSH
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
	#[Common\Meta\PropertyFilter([ Common\Filters\Lists::class, 'ArrayOf' ])]
	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Datastore
	$Domains = [ ];

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
			'Domains'    => $this->Domains->GetData(),
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

		// the Domains option is meant to provide the same support as the
		// web.atl version where each line is a different ssl cert.

		// however this current implementation only supports generating
		// one acmephp file where the first domain it encounters in the
		// config becomes the primary and then every other are alt domains.

		// idealy each line would be able to generate its own .yml file.

		$WebRoot = $App->GetWebRoot();

		// generate a merged list of values.

		$Values = Common\Datastore::FromStackMerged($this->ToArray(), [
			'WebRoot'    => $WebRoot,
			'CertRoot'   => $CertRoot,
			'AltDomains' => []
		]);

		////////

		if(!count($Values['Domains']))
		throw new Common\Error\RequiredDataMissing('Domains', 'at least one DomainLine');

		Common\Datastore::FromArray($Values['Domains'])
		->Each(function(string $Line) use($Values) {

			$Domain = new Atlantis\Struct\DomainLine($Line);

			if(!$Values['Domain'])
			$Values['Domain'] = $Domain->Primary;

			$Values['AltDomains'] = array_merge(
				$Values['AltDomains'],
				$Domain->ToList()
			);

			return;
		});

		////////

		// try to load the template file.

		$File = Common\Filesystem\Util::Pathify(
			$App->FromProjectRoot(),
			'vendor', 'netherphp', 'atlantis',
			'templates', 'acmephp.txt'
		);

		$Template = Common\TemplateFile::FromFile($File);

		// bake the values as needed before shipping them out.

		$Values->RemapKeys(function(string $K, mixed $V) {

			if(!is_object($V) && !is_array($V))
			$V = trim(Common\Filters\Text::EscapeSingleQuoteYAML($V));

			else
			$V = trim(
				Common\Datastore::FromArray($V)
				->Filter(function(string $V) { return !!$V; })
				->Accumulate('', fn(string $P, string $N)=> sprintf(
					'%s      - \'%s\'%s',
					$P,
					Common\Filters\Text::EscapeSingleQuoteYAML($N),
					PHP_EOL
				))
			);

			return [ $K=> $V ];
		});

		return $Template->ReplaceTokensWith($Values);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasAnything():
	bool {

		if(isset($this->Type))
		if(isset($this->Domains) && $this->Domains->Count())
		return TRUE;

		return FALSE;
	}

};
