<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\Theme;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

################################################################################
################################################################################

#[Common\Meta\Info('Use in design.phtml files to load the parent theme design.phtml and provide overwriting config.')]
class TemplateExtender {

	protected Surface\Engine
	$Engine;

	protected array
	$Scope = [];

	protected string
	$Theme = 'default';

	protected mixed
	$OnAtlConfig = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Invoke(?callable $OnAtlConfig=NULL):
	void {

		$this->OnAtlConfig = match(TRUE) {
			(is_callable($OnAtlConfig))
			=> $OnAtlConfig,

			default
			=> (function(Atlantis\Engine $App, Surface\Engine $Surface, ...$__ARGS) {
				return;
			})
		};

		////////

		$this->Run();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Run():
	void {

		if(!array_key_exists('Surface', $this->Scope))
		throw new Common\Error\RequiredDataMissing(
			'Surface', Surface\Engine::class
		);

		////////

		$Box = (function(string $__TROOT, string $__TNAME, callable $__FCFG, array $__SCOPE) {

			/**
			 * @var Surface\Engine $Surface
			 */

			extract($__SCOPE);

			if(is_callable($__FCFG))
			$__FCFG(...$__SCOPE);

			require(sprintf('%s/%s/design.phtml', $__TROOT, $__TNAME));

			return;
		});

		$Box(
			$this->Engine->ThemeRoot,
			$this->Theme,
			$this->OnAtlConfig,
			$this->Scope
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromSurfaceWithScope(Surface\Engine $Surface, array $Scope):
	static {

		$Output = new static;
		$Output->Engine = $Surface;
		$Output->Scope = $Scope;

		////////

		return $Output;
	}

	static public function
	FromDesignScope(array $Scope):
	static {

		if(!array_key_exists('Surface', $Scope))
		throw new Common\Error\RequiredDataMissing(
			'Surface',
			Surface\Engine::class
		);

		////////

		return static::FromSurfaceWithScope($Scope['Surface'], $Scope);
	}

};
