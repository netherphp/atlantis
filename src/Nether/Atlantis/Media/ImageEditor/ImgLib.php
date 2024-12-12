<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media\ImageEditor;

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
			($Use === static::Imagick)
			=> new ImgLibImagick,

			default
			=> new ImgLibGD
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
	////////////////////////////////////////////////////////////////

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

	////////////////////////////////
	////////////////////////////////

	abstract public function
	Fit(int $W, int $H):
	static;

	abstract public function
	RemoveMetadata():
	static;

};
