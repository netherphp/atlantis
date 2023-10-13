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
	ToAcmeYaml(string $WebRoot, string $CertRoot='/opt/ssl'):
	string {

		// @todo 2023-10-13
		// this will eventually be done by a service interface during
		// phase two of the ssl handling update.

		$Data = (
			Common\Datastore::FromStackMerged($this->ToArray(), [
				'WebRoot'  => $WebRoot,
				'CertRoot' => $CertRoot
			])
			->Remap(function(mixed $V) {
				if(is_string($V))
				return preg_replace("#'{1}#", "''", $V);

				if($V instanceof Common\Datastore)
				return $V->Map(fn(string $W)=> addslashes($W));

				return $V;
			})
		);

		////////

		$Lines = new Common\Datastore([
			"contact_email: '{$Data['Email']}'",
			"certificates:",
			"  - domain: '{$Data['Domain']}'",
			"    subject_alternative_names:",
			"      - '{$Data['Domain']}'"
		]);

		array_map(
			function(string $D) use($Lines) { $Lines->Push("      - '{$D}'"); return; },
			$Data['AltDomains']
		);

		$Lines->MergeRight([
			"    distinguished_name:",
			"      organization_name: '{$Data['OrgName']}'",
			"      country: '{$Data['Country']}'",
			"      locality: '{$Data['City']}'",
			"    solver:",
			"      name: 'http-file'",
			"      adapter: 'local'",
			"      root: '{$Data['WebRoot']}'",
			"    install:",
			"      - action: 'mirror_file'",
			"        adapter: 'local'",
			"        root: '{$Data['CertRoot']}'"
		]);

		return $Lines->Join(chr(10));
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
