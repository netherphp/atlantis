<?php

namespace Routes;

use Nether\Atlantis\Routes\Web;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Avenue\Meta\ConfirmWillAnswerRequest;
use Nether\Avenue\Response;
use Nether\Sensei\Struct\CodebaseStats;

class Docs
extends Web {

	#[RouteHandler('/docs')]
	public function
	Index():
	void {

		$Filename = $this->GetDocsFile('index');
		$Codebase = NULL;
		$Stats = NULL;

		if(file_exists($Filename)) {
			$Codebase = unserialize(file_get_contents($Filename));
			$Stats = new CodebaseStats($Codebase);
		}

		$this->App->Surface
		->Area('sensei/index', [
			'Codebase'      => $Codebase,
			'CodebaseStats' => $Stats
		]);

		return;
	}

	#[RouteHandler('/docs/:Path:')]
	#[ConfirmWillAnswerRequest]
	public function
	View(string $Path):
	void {

		$Filename = $this->GetDocsFile($Path);
		$Codebase = unserialize(file_get_contents($Filename));

		($this->App->Surface)
		->Area('sensei/class', [
			'Class' => $Codebase
		]);

		return;
	}

	public function
	ViewWillAnswerRequest(string $Path):
	int {

		$Filename = $this->GetDocsFile($Path);

		////////

		if(!file_exists($Filename))
		return Response::CodeNotFound;

		if(!is_readable($Filename))
		return Response::CodeForbidden;

		////////

		return Response::CodeOK;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetDocsFile(string $Path):
	string {

		return sprintf(
			'%s/docs/phson/%s.phson',
			$this->App->GetProjectRoot(),
			$Path
		);
	}

}