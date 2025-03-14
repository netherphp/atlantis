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

		$this->ImgLib = ImageEditor\ImgLib::New(
			ImageEditor\ImgLib::Imagick
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	// FILE MANAGEMENT /////////////////////////////////////////////

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

	////////////////////////////////
	////////////////////////////////

	public function
	IsOpen():
	bool {

		return $this->ImgLib->IsOpen();
	}

	public function
	GetRaw():
	mixed {

		return $this->ImgLib->GetRaw();
	}

	////////////////////////////////////////////////////////////////
	// GEOMETRY ////////////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	public function
	GetHeight():
	int {

		return $this->ImgLib->GetHeight();
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	GetWidth():
	int {

		return $this->ImgLib->GetWidth();
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Fit(int $W, int $H):
	static {

		$this->ImgLib->Fit($W, $H);

		return $this;
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	FitForColumnsIn(ImageEditor $Base, int $Columns=0):
	static {

		$this->ImgLib->FitForColumnsIn($Base, $Columns);

		return $this;
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	Rotate(float $Degrees):
	static {

		$this->ImgLib->Rotate($Degrees);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// COLOUR FILTERS //////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	public function
	FilterInvert():
	static {

		$this->ImgLib->FilterInvert();

		return $this;
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	FilterAlphaMult(float $Mult):
	static {

		$this->ImgLib->FilterAlphaMult($Mult);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// COMPOSITION /////////////////////////////////////////////////

	public function
	CompositeTiled(ImageEditor $Overlay):
	static {

		$this->ImgLib->OverlayTiled($Overlay);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// TEXT STUFF //////////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	public function
	AnnotateBottom(string|array $Text, int $TSize=20, int $TPad=4, string $TColour='red'):
	static {

		$this->ImgLib->AnnotateBottom($Text, $TSize, $TPad, $TColour);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// METADATA ////////////////////////////////////////////////////

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