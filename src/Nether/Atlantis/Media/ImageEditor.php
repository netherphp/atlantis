<?php ##########################################################################
################################################################################

// $Img = ImageEditor::FromFile($FilenameOpen);
// $Img->Fit(1280, 720);
// $Img->Save($FilenameSave);

namespace Nether\Atlantis\Media;

use Nether\Atlantis;
use Nether\Common;

use GdImage;
use Imagick;

################################################################################
################################################################################

class ImageEditor {

	protected string
	$Filename;

	protected ImageEditor\ImgLib
	$ImgLib;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct() {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Open(string $Filename):
	static {

		$this->ImgLib->Open($Filename);
		$this->Filename = $Filename;

		return $this;
	}

	public function
	Close():
	static {

		$this->ImgLib->Close();

		return $this;
	}

	public function
	Save(?string $Filename=NULL):
	static {

		$Filename ??= $this->Filename;

		////////

		$this->ImgLib->Save($Filename);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Fit(int $W, int $H):
	static {

		$this->ImgLib->Fit($W, $H);

		return $this;
	}

	public function
	RemoveMetadata():
	static {

		$this->ImgLib->RemoveMetadata();

		return $this;
	}


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $Filename):
	static {

		$Output = new ImageEditor;
		$Output->Open($Filename);

		return $Output;
	}

};