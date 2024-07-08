<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Struct;

use Nether\Common;
use Nether\Dye;

// dyes.json notes:
//
// it is possible to create an infinite loop with the aliases i have not done
// anything to detect that yet so heads up about being bad at that.
//
// | @alias | link to the bg of another colour row
// | &alias | link to the fg of another colour row
// | %auto  | automatically detect and select dark light pairing for this row.

################################################################################
################################################################################

class DyeSet
extends Common\Prototype {

	#[Common\Meta\PropertyFactory('FromArray')]
	public array|Common\Datastore
	$Items = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Import(iterable $Input):
	static {

		($this->Items)
		->Import($Input)
		->Remap($this->Convert(...));

		return $this;
	}

	public function
	Convert(string|array $Input):
	DyeSetPair {

		$Kit = [ 'Bg'=> '', 'Fg'=> '' ];
		$Output = NULL;

		////////

		if(is_array($Input)) {
			$Kit['Bg'] = $Input['Bg'];
			$Kit['Fg'] = $Input['Fg'];
		}

		else {
			$Kit['Bg'] = $Input;
			$Kit['Fg'] = $Input;
		}

		////////

		$Output = new DyeSetPair($Kit);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	EtchRootBlock(Common\Datastore $Tablet):
	static {

		$Tablet->Push(':root {');
		$this->Items->EachKeyValue(function(string $Key, DyeSetPair $P) use($Tablet) {
			$Bg = $this->GetBg($Key);
			$BgAlt = $this->GetBg($Key)->Lighten(0.1)->Desaturate(0.1);
			$Fg = $this->GetFg($Key);
			$FgAlt = $this->GetFg($Key);

			($Tablet)
			->Push("\t--theme-{$Key}: {$Bg->ToHexRGBA()};")
			->Push("\t--theme-{$Key}-alt: {$BgAlt->Lighten(0.1)->Desaturate(0.1)->ToHexRGBA()};")
			->Push("\t--theme-{$Key}-fg: {$Fg->ToHexRGBA()};")
			->Push("\t--theme-{$Key}-alt-fg: {$FgAlt->ToHexRGBA()};");

			return;
		});
		$Tablet->Push('}');
		$Tablet->Push('');

		$Tablet->Push('[data-bs-theme="light"]:root {');
		Common\Datastore::FromArray([ [ 'dark', 'light' ], [ 'light', 'dark' ] ])
		->Each(function(array $K) use($Tablet) {

			$Bg = $this->GetBg($K[1]);
			$BgAlt = $this->GetBg($K[1])->Lighten(0.2)->Desaturate(0.1);
			$Fg = $this->GetFg($K[1]);
			$FgAlt = $this->GetFg($K[1]);

			($Tablet)
			->Push("\t--theme-{$K[0]}: {$Bg->ToHexRGBA()};")
			->Push("\t--theme-{$K[0]}-alt: {$BgAlt->Lighten(0.1)->Desaturate(0.1)->ToHexRGBA()};")
			->Push("\t--theme-{$K[0]}-fg: {$Fg->ToHexRGBA()};")
			->Push("\t--theme-{$K[0]}-alt-fg: {$FgAlt->ToHexRGBA()};");

			return;
		});
		$Tablet->Push('}');
		$Tablet->Push('');

		return $this;
	}

	public function
	EtchButtonsBS5(Common\Datastore $Tablet):
	static {

		$LP = '[data-bs-theme="light"]';

		// render out all the buttons.

		$this->Items->EachKeyValue(fn(string $K)=>
			($Tablet)
			->Push(".btn-{$K} {")
			->Push("	background-color: var(--theme-{$K});")
			->Push("	border: var(--bs-btn-border-width) solid var(--theme-{$K});")
			->Push("	color: var(--theme-{$K}-fg);")
			->Push("}")
			->Push("")
			->Push(".btn-{$K}:active, .btn-{$K}:hover, .btn-{$K}:focus .btn-{$K}.Show {")
			->Push("	background-color: var(--theme-{$K}-alt);")
			->Push("	border: var(--bs-btn-border-width) solid var(--theme-{$K}-alt);")
			->Push("	color: var(--theme-light-fg);")
			->Push("}")
			->Push("")
			->Push(".btn-outline-{$K} {")
			->Push("	background-color: transparent;")
			->Push("	border: var(--bs-btn-border-width) solid var(--theme-{$K});")
			->Push("	color: var(--theme-light);")
			->Push("}")
			->Push("")
			->Push(".btn-outline-{$K}:active, .btn-outline-{$K}:hover, .btn-outline-{$K}:focus .btn-outline-{$K}.show {")
			->Push("	background-color: var(--theme-{$K}-alt);")
			->Push("	border: var(--bs-btn-border-width) solid var(--theme-{$K}-alt);")
			->Push("	color: var(--theme-light-fg);")
			->Push("}")
			->Push("")
		);

		// render light/dark inversions.

		/*

		Common\Datastore::FromArray([ [ 'dark', 'light' ], [ 'light', 'dark' ] ])
		->Each(fn(array $K)=>
			($Tablet)
			->Push("{$LP} .btn-{$K[0]} {")
			->Push("	background-color: var(--theme-{$K[1]});")
			->Push("	border-color: var(--theme-{$K[1]});")
			->Push("	color: var(--theme-{$K[1]}-fg);")
			->Push("}")
			->Push("")
			->Push("{$LP} .btn-{$K[0]}:active, {$LP} .btn-{$K[0]}:hover, {$LP} .btn-{$K[0]}:focus {$LP} .btn-{$K[0]}.Show {")
			->Push("	background-color: var(--theme-{$K[1]}-alt);")
			->Push("	border-color: var(--theme-{$K[1]}-alt);")
			->Push("	color: var(--theme-{$K[1]}-alt-fg);")
			->Push("}")
			->Push("")
			->Push("{$LP} .btn-outline-{$K[0]} {")
			->Push("	background-color: transparent;")
			->Push("	border-color: var(--theme-{$K[1]});")
			->Push("	color: var(--theme-{$K[1]});")
			->Push("}")
			->Push("")
			->Push("{$LP} .btn-outline-{$K[0]}:active, {$LP} .btn-outline-{$K[0]}:hover, {$LP} .btn-outline-{$K[0]}:focus {$LP} .btn-outline-{$K[0]}.show {")
			->Push("	background-color: var(--theme-{$K[1]}-alt);")
			->Push("	border-color: var(--theme-{$K[1]}-alt);")
			->Push("	color: var(--theme-{$K[1]}-alt-fg);")
			->Push("}")
			->Push("")
		);

		*/

		return $this;;
	}

	public function
	EtchBgFgBS5(Common\Datastore $Tablet):
	static {

		$this->Items->EachKeyValue(fn(string $K)=>
			($Tablet)
			->Push(".bg-{$K} { background-color: var(--theme-{$K}) !important; }")
			->Push(".tc-{$K} { color: var(--theme-{$K}) !important; }")
			->Push(".fg-{$K} { color: var(--theme-{$K}-fg) !important; }")
			->Push(".bg-{$K}-alt { background-color: var(--theme-{$K}-alt) !important; }")
			->Push(".tc-{$K}-alt { color: var(--theme-{$K}-alt) !important; }")
			->Push(".fg-{$K}-alt { color: var(--theme-{$K}-alt-fg) !important; }")
			->Push("")
		);

		return $this;
	}

	public function
	EtchBordersBS5(Common\Datastore $Tablet):
	static {

		$this->Items->EachKeyValue(fn(string $K)=>
			($Tablet)
			->Push(".bc-{$K} { --bs-border-color: var(--theme-{$K}); }")
			->Push("")
		);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetFg(string $For):
	Dye\Colour {

		/** @var DyeSetPair $Pair */
		$Pair = $this->Items[$For];

		////////

		if($Pair->Fg === '%auto') {
			$Other = $this->GetBg($For);

			if($Other->IsBright())
			return $this->GetFg('light');
			else
			return $this->GetFg('dark');
		}

		if(str_starts_with($Pair->Fg, '@'))
		return $this->GetBg(substr($Pair->Fg, 1));

		if(str_starts_with($Pair->Fg, '&'))
		return $this->GetFg(substr($Pair->Fg, 1));

		////////

		$Dye = Dye\Colour::From($this->Items[$For]->Fg);

		return $Dye;
	}

	public function
	GetBg(string $For):
	Dye\Colour {

		/** @var DyeSetPair $Pair */
		$Pair = $this->Items[$For];

		////////

		if($Pair->Bg === '%auto') {
			$Other = $this->GetFg($For);

			if($Other->IsBright())
			return $this->GetBg('light');
			else
			return $this->GetBg('dark');
		}

		if(str_starts_with($Pair->Bg, '@'))
		return $this->GetBg(substr($Pair->Bg, 1));

		if(str_starts_with($Pair->Bg, '&'))
		return $this->GetFg(substr($Pair->Bg, 1));

		////////

		$Dye = Dye\Colour::From($this->Items[$For]->Bg);

		return $Dye;
	}

	public function
	GetBgRGBA(string $For):
	string {

		return $this->GetBg($For)->ToHexRGBA();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $Filename):
	static {

		$JSON = Common\Filesystem\Util::TryToReadFileJSON($Filename);

		$Output = new static;
		$Output->Import($JSON);

		return $Output;
	}

};
