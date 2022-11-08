<?php

namespace Routes;
use Nether;

use Nether\Atlantis\Routes\Web;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Avenue\Meta\ConfirmWillAnswerRequest;
use Nether\Avenue\Response;
use Nether\Sensei\Struct\CodebaseStats;
use Nether\Sensei\Inspectors\NamespaceInspector;
use Nether\Sensei\Inspectors\ClassInspector;

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

			$Codebase->Namespaces
			->Filter(
				fn(NamespaceInspector $NS)
				=> $NS->Classes->Count()
			)
			->Each(
				fn(NamespaceInspector $NS)
				=> $NS->SortForPresentation()
			);
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
		$Area = NULL;
		$Scope = [];

		////////

		if($Codebase instanceof NamespaceInspector) {
			$Area = 'sensei/namespace';
			$Scope['Namespace'] = $Codebase;
		}

		if($Codebase instanceof ClassInspector) {
			$Area = 'sensei/class';
			$Scope['Class'] = $Codebase;
		}

		if(!isset($GLOBALS['SenseiBuiltinData']))
		$GLOBALS['SenseiBuiltinData'] = unserialize(file_get_contents(sprintf(
			'%s/sensei-builtin.phson',
			ProjectRoot
		)));

		if($Area === NULL) {
			($this->Response)
			->SetCode(404);

			($this->App->Surface)
			->Area('error/not-found');

			return;
		}

		////////

		($this->App->Surface)
		->Area($Area, $Scope);

		return;
	}

	public function
	ViewWillAnswerRequest(string $Path):
	int {

		$Filename = $this->GetDocsFile($Path);

		if(!file_exists($Filename))
		return Response::CodeNope;

		return Response::CodeOK;
	}

	#[RouteHandler('/docs/:Path:')]
	#[ConfirmWillAnswerRequest]
	public function
	Page(string $Path):
	void {

		$Path = Nether\Avenue\Util::MakePathableKey($Path);

		($this->App->Surface)
		->Area("sensei/pages/{$Path}");

		return;
	}

	public function
	PageWillAnswerRequest(string $Path):
	int {

		$Path = Nether\Avenue\Util::MakePathableKey($Path);
		$Filename = $this->GetPageFile($Path);

		if(!file_exists($Filename))
		return Response::CodeNope;

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

	protected function
	GetPageFile(string $Path):
	string {

		return sprintf(
			'%s/www/themes/default/area/sensei/pages/%s.phtml',
			$this->App->GetProjectRoot(),
			$Path
		);
	}

}