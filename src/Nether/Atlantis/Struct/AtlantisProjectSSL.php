<?php

namespace Nether\Atlantis\Struct;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;

use JsonSerializable;
use Stringable;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class AtlantisProjectSSL
extends Common\Prototype
implements
	JsonSerializable,
	Stringable {

	public string
	$Service = 'AcmePHP';

	public string
	$Domain = '';

	#[Common\Meta\PropertyFactory('FromArray', 'AltDomains')]
	public array|Common\Datastore
	$AltDomains = [];

	// organization/company info

	public string
	$OrgName = '';

	public string
	$Country = '';

	public string
	$City = '';

	// tech contact info

	public string
	$Email = '';

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS: JsonSerializable ////////////////////////////////

	public function
	JsonSerialize():
	mixed {

		return $this->ToArray();
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS: Stringable //////////////////////////////////////

	public function
	__ToString():
	string {

		return $this->ToString();
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

		if($this->Domain)
		return TRUE;

		return FALSE;
	}

	public function
	ToArray():
	array {

		return [
			'Service'    => $this->Service,
			'Domain'     => $this->Domain,
			'AltDomains' => $this->AltDomains->GetData(),
			'OrgName'    => $this->OrgName,
			'Country'    => $this->Country,
			'City'       => $this->City,
			'Email'      => $this->Email
		];
	}

	public function
	ToArrayFlat():
	array {

		return array_map(
			fn(mixed $V)
			=> match(TRUE) {
				(is_object($V) || is_array($V))
				=> json_encode($V),

				default
				=> $V
			},
			$this->ToArray()
		);
	}

	public function
	ToJSON():
	string {

		return Common\Filters\Text::Tabbify(
			json_encode($this, JSON_PRETTY_PRINT)
		);
	}

	public function
	ToString():
	string {

		return $this->ToJSON();
	}

};
