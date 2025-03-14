<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media\ImageEditor;

use Nether\Atlantis\Media\ImageEditor;
use Nether\Common;


################################################################################
################################################################################

abstract class ImgLib {

	const
	Imagick = 'Imagick',
	GD      = 'GdImage';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public string
	$Use = self::Imagick;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(?string $Use=NULL):
	static {

		$Use ??= static::$Use;

		$Output = match($Use) {
			static::Imagick
			=> new ImgLibImagick,

			static::GD
			=> new ImgLibGD,

			default
			=> throw new Common\Error\ConfigNotFound($Use)
		};

		return $Output;
	}

	static public function
	FromFile(string $Filename):
	static {

		$Output = static::New();
		$Output->Open($Filename);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	// FILE MANAGEMENT /////////////////////////////////////////////

	abstract public function
	Open(string $Filename):
	static;

	abstract public function
	Close():
	static;

	abstract public function
	Save(string $Filename):
	static;

	////////////////////////////////
	////////////////////////////////

	abstract public function
	IsOpen():
	bool;

	////////////////////////////////////////////////////////////////
	// GEOMETRY ////////////////////////////////////////////////////

	abstract public function
	Fit(int $W, int $H):
	static;

	#[Common\Meta\Date('2025-03-13')]
	abstract public function
	FitForColumnsIn(ImageEditor $Base, int $Columns=0):
	static;

	#[Common\Meta\Date('2025-03-13')]
	abstract public function
	Rotate(float $Degrees):
	static;

	////////////////////////////////
	////////////////////////////////

	abstract public function
	GetRaw():
	mixed;

	#[Common\Meta\Date('2025-03-13')]
	abstract public function
	GetHeight():
	int;

	#[Common\Meta\Date('2025-03-13')]
	abstract public function
	GetWidth():
	int;

	////////////////////////////////////////////////////////////////
	// COLOUR FILTERS //////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	abstract public function
	FilterInvert():
	static;

	#[Common\Meta\Date('2025-03-13')]
	abstract public function
	FilterAlphaMult(float $Mult):
	static;

	////////////////////////////////////////////////////////////////
	// COMPOSITION /////////////////////////////////////////////////

	abstract public function
	OverlayTiled(ImageEditor $Overlay):
	static;

	////////////////////////////////////////////////////////////////
	// TEXT STUFF //////////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	abstract public function
	AnnotateBottom(string|array $Text, int $TSize, int $TPad, string $TColour):
	static;

	////////////////////////////////////////////////////////////////
	// METADATA ////////////////////////////////////////////////////

	abstract public function
	RemoveMetadata():
	static;

};
