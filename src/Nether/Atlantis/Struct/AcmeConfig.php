<?php

namespace Nether\Atlantis\Struct;

use Exception;
use Nether\Atlantis\Engine;
use Nether\Atlantis\Library;
use Nether\Object\Datastore;

class AcmeConfig {

	public ?string
	$Phar;

	public ?string
	$WebRoot;

	public ?string
	$CertRoot;

	public ?string
	$Domain;

	public ?string
	$Email;

	public ?array
	$AltDomains;

	public ?string
	$Country;

	public ?string
	$City;

	public ?string
	$OrgName;

	public function
	__Construct(Engine $App) {

		$this->Phar = $App->Config[Library::ConfAcmePhar] ?: NULL;
		$this->CertRoot = $App->Config[Library::ConfAcmeCertRoot] ?: NULL;
		$this->WebRoot = $App->GetWebRoot() ?: NULL;

		$this->Domain = $App->Config[Library::ConfAcmeDomain] ?: NULL;
		$this->Email = $App->Config[Library::ConfAcmeEmail] ?: NULL;
		$this->Country = $App->Config[Library::ConfAcmeCountry] ?: NULL;
		$this->City = $App->Config[Library::ConfAcmeCity] ?: NULL;
		$this->OrgName = $App->Config[Library::ConfAcmeOrgName] ?: NULL;
		$this->AltDomains = [ $this->Domain ];

		////////

		// your main domain seems to also be listed as an alternate
		// or else the alternate chain does not seem to work right when
		// hitting letsencrypt.

		$Alts = $App->Config[Library::ConfAcmeAltDomains];

		if(is_array($Alts))
		$this->AltDomains = array_merge($this->AltDomains, $Alts);

		////////

		return;
	}

	public function
	IsMissingConfig():
	bool {

		return (count($this->GetMissingConfig()) === 0);
	}

	public function
	GetMissingConfig():
	array {

		$Output = [];
		$Prop = NULL;
		$Val = NULL;

		foreach($this as $Prop => $Val)
		switch($Prop) {
			default:
				if(!isset($Val))
				$Output[] = "AcmePHP.{$Prop}";
			break;
		}

		return $Output;
	}

	public function
	GenerateConfigData(string $TemplatePath):
	string {

		if(!is_readable($TemplatePath))
		throw new Exception("unable to read {$TemplatePath}");

		////////

		$Output = file_get_contents($TemplatePath);
		$Match = NULL;
		$Token = NULL;
		$Key = NULL;

		// 1 = indent
		// 2 = token

		preg_match_all('/(\x20*){(.+?)}/', $Output, $Match);

		foreach($Match[2] as $Key => $Token)
		switch($Token) {
			case 'AltDomains':
				$Output = str_replace(
					sprintf('{%s}', $Token),
					trim(join(
						"\n{$Match[1][$Key]}",
						array_map(
							(fn($Val)=> "- '{$Val}'"),
							$this->AltDomains
						)
					)),
					$Output
				);
			break;
			default:
				$Output = str_replace(
					sprintf('{%s}', $Token),
					$this->{$Token},
					$Output
				);
			break;
		}

		return $Output;
	}

}
