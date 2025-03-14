<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media\ImageEditor;

use Nether\Common;
use Nether\Atlantis\Media\ImageEditor;

use Imagick;
use ImagickPixel;
use ImagickDraw;
use ImagickPixelIterator;
use Exception;

################################################################################
################################################################################

class ImgLibImagick
extends ImgLib {

	protected Imagick
	$Img;

	////////////////////////////////////////////////////////////////
	// FILE MANAGEMENT /////////////////////////////////////////////

	public function
	GetRaw():
	mixed {

		return $this->Img;
	}

	public function
	IsOpen():
	bool {

		return isset($this->Img);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Open(string $Filename):
	static {

		if(!file_exists($Filename))
		throw new Common\Error\FileNotFound($Filename);

		if(!is_readable($Filename))
		throw new Common\Error\FileUnreadable($Filename);

		if($this->IsOpen())
		$this->Close();

		////////

		try {
			$this->Img = new Imagick($Filename);
			($this->Img)->resetIterator();
		}

		catch(Exception $Err) {
			throw $Err;
		}

		////////

		return $this;
	}

	public function
	Close():
	static {

		if(isset($this->Img))
		($this->Img)->clear();

		return $this;
	}

	public function
	Save(string $Filename):
	static {

		($this->Img)->writeImage($Filename);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// GEOMETRY ////////////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	public function
	GetHeight():
	int {

		return $this->Img->getImageHeight();
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	GetWidth():
	int {

		return $this->Img->getImageWidth();
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Fit(int $W, int $H):
	static {

		$this->Img->ResizeImage(
			$W, $H,
			Imagick::FILTER_LANCZOS, 1.0,
			TRUE
		);

		return $this;
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	FitForColumnsIn(ImageEditor $Base, int $Columns=0):
	static {

		$this->Img->ResizeImage(
			($Base->GetWidth() / $Columns), -1,
			Imagick::FILTER_LANCZOS2, 1.0,
			FALSE
		);

		return $this;
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	Rotate(float $Degrees):
	static {

		$Transparent = new ImagickPixel('transparent');

		($this->Img)
		->rotateImage($Transparent, $Degrees);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// COLOUR FILTERS //////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	public function
	FilterInvert():
	static {

		$Greyscale = FALSE;

		$Channels = (0
			| Imagick::CHANNEL_RED
			| Imagick::CHANNEL_GREEN
			| Imagick::CHANNEL_BLUE
		);

		($this->Img)->negateImage($Greyscale, $Channels);

		return $this;
	}

	#[Common\Meta\Date('2025-03-13')]
	public function
	FilterAlphaMult(float $Mult):
	static {

		// this exists because Imagick::setImageOpacity sets every pixel
		// to that exact opacity, causing edges of transparent things to
		// loose their antialiasing or look bad all bloomed out. this way
		// will apply the multiplier to each pixel to maintain the quality
		// of the alpha edges.

		$Iter = ($this->Img)->getPixelIterator();
		$Row = NULL;
		$Pixel = NULL;

		////////

		while($Row = ($Iter)->getNextIteratorRow()) {
			/** @var array $Row */

			foreach($Row as $Pixel) {
				/** @var ImagickPixel $Pixel */

				($Pixel)->setColorValue(
					Imagick::COLOR_ALPHA,
					(($Pixel)->getColorValue(Imagick::COLOR_ALPHA) * $Mult)
				);

				continue;
			}

			($Iter)->syncIterator();

			continue;
		}

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// COMPOSITION /////////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	public function
	OverlayTiled(ImageEditor $Overlay):
	static {

		$Over = $Overlay->GetRaw();
		$New = ($this->Img)->textureImage($Over);

		($this->Img)->clear();

		$this->Img = $New;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// TEXT STUFF //////////////////////////////////////////////////

	#[Common\Meta\Date('2025-03-13')]
	public function
	AnnotateBottom(string|array $Text, int $TSize, int $TPad, string $TColour):
	static {

		$Draw = new ImagickDraw;
		$TCol = new ImagickPixel($TColour);
		$NCol = new ImagickPixel('transparent');
		$Layer = new Imagick;
		$Pos = new Common\Units\Vec2(0, $TPad);

		// turn a text array into a single string.

		if(is_array($Text))
		$Text = trim(join("\n", $Text));

		// we will draw on a new layer so we can do post processing later.
		// (like a drop shadow)

		$Layer->newImage(
			$this->Img->getImageWidth(),
			$this->Img->getImageHeight(),
			$NCol
		);

		$Draw->setGravity(Imagick::GRAVITY_SOUTH);
		$Draw->setFont('Helvetica-Bold');
		$Draw->setFontSize($TSize);
		$Draw->setFillColor($TCol);
		$Draw->setTextAntialias(TRUE);

		$Draw->annotation($Pos->X, $Pos->Y, $Text);
		$Layer->drawImage($Draw);

		$this->Img->compositeImage(
			$Layer, Imagick::COMPOSITE_ATOP,
			0, 0
		);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	// METADATA ////////////////////////////////////////////////////

	public function
	RemoveMetadata():
	static {

		($this->Img)->stripImage();

		return $this;
	}

};
