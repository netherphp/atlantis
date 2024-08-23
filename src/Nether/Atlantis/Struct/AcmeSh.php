<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;
use Nether\Console;

class AcmeSh {

	public string
	$Root;

	public string
	$BinPath;

	public string
	$ConfPath;

	public string
	$CertPath;

	public string
	$Server;

	public Common\Datastore
	$Domains;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Root, Common\Datastore $Domains, string $Server='letsencrypt') {

		$this->PreparePaths($Root);
		$this->Domains = $Domains;
		$this->Server = $Server;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Run(?string $Webroot=NULL):
	bool {

		if($Webroot !== NULL)
		return $this->RunModeWebroot($Webroot);

		return FALSE;
	}

	public function
	RunModeWebroot(string $Webroot):
	bool {

		$Base = $this->BuildBaseCmd();
		$Mode = $this->DetermineMode();
		$Domains = $this->BuildDomainArgs();
		$Webroot = escapeshellarg($Webroot);
		$Cmd = NULL;
		$CLI = NULL;

		////////

		$Cmd = sprintf(
			'%s %s %s --server letsencrypt -w %s',
			$Base, $Mode, $Domains, $Webroot
		);

		$CLI = new Console\Struct\CommandLineUtil($Cmd);
		$CLI->Run();

		if($CLI->HasError()) {
			echo $CLI->GetOutputString(), PHP_EOL, PHP_EOL;
			return FALSE;
		}

		return TRUE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	PreparePaths(string $Root):
	void {

		$this->Root = $Root;
		$this->BinPath = sprintf('%s/acme.sh', $this->Root);
		$this->ConfPath = sprintf('%s/local/confs', $this->Root);
		$this->CertPath = sprintf('%s/local/certs', $this->Root);

		return;
	}

	protected function
	DeterminePrimaryDomain():
	string {

		return escapeshellarg($this->Domains->Values()[0]);
	}

	protected function
	DetermineMode():
	string {

		$Base = $this->BuildBaseCmd();
		$Domain = $this->DeterminePrimaryDomain();
		$Cmd = NULL;
		$CLI = NULL;

		////////

		$Cmd = sprintf(
			'%s --list | grep %s',
			$Base, $Domain
		);

		$CLI = new Console\Struct\CommandLineUtil($Cmd);
		$CLI->Run();

		if($CLI->HasOutput())
		return '--renew --force';

		return '--issue';
	}

	protected function
	BuildBaseCmd():
	string {

		$Output = sprintf(
			'%s --home %s --config-home %s',
			escapeshellarg($this->BinPath),
			escapeshellarg($this->Root),
			escapeshellarg($this->ConfPath)
		);

		return $Output;
	}

	protected function
	BuildDomainArgs():
	string {

		$Domains = $this->Domains->Map(
			fn(string $D)
			=> sprintf('-d %s', escapeshellarg($D))
		);

		$Output = $Domains->Join(' ');

		return $Output;
	}

};
