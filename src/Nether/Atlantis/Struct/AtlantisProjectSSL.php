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

		// @todo 2023-10-13
		// this will eventually be done by a service interface during
		// phase two of the ssl handling update.

		$TemplateFile = Common\Filesystem\Util::Pathify(
			$App->FromProjectRoot(),
			'vendor', 'netherphp', 'atlantis',
			'templates', 'acmephp.txt'
		);

		if(!file_exists($TemplateFile))
		throw new Common\Error\FileNotFound($TemplateFile);

		if(!is_readable($TemplateFile))
		throw new Common\Error\FileUnreadable($TemplateFile);

		// generate the acmephp.yml final file filling in all the tokens
		// from the template with final data.

		$TemplateData = file_get_contents($TemplateFile);
		$TemplateTokens = Common\Text::TemplateFindTokens($TemplateData);
		$TemplateValues = (
			// merge datasets.
			Common\Datastore::FromStackMerged($this->ToArray(), [
				'WebRoot'  => $WebRoot,
				'CertRoot' => $CertRoot
			])

			// turning ' into '' seems to be how yaml escapes omfg.
			->Remap(function(mixed $V) {
				if(is_string($V))
				return preg_replace("#'{1}#", "''", $V);

				if($V instanceof Common\Datastore)
				return $V->Map(fn(string $W)=> preg_replace("#'{1}#", "''", $W));

				return $V;
			})

			// handle special dataatypes.
			->MapKeys(function(string $K, mixed $V) {
				return [ $K=> match($K) {
					'AltDomains'
					=> trim(array_reduce(
						$V,
						fn(string $P, string $N)
						=> sprintf('%s      - \'%s\'%s', $P, $N, PHP_EOL),
						''
					)),

					default
					=> trim($V)
				} ];
			})
		);

		// at some point found out that adding the primary domain as an
		// alternate domain would fix some problem where they said no.

		$TemplateValues['AltDomains'] = trim(sprintf(
			'- \'%s\'%s      %s',
			$TemplateValues['Domain'],
			PHP_EOL,
			$TemplateValues['AltDomains']
		));

		// compile the final data to write to disk.

		$TemplateData = $TemplateTokens->Accumulate(
			$TemplateData,
			function(string $Output, string $Current)
			use($TemplateValues) {
				return str_replace(
					Common\Text::TemplateMakeToken($Current),
					($TemplateValues[$Current] ? $TemplateValues[$Current] : ''),
					$Output
				);
			}
		);

		return $TemplateData;
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
