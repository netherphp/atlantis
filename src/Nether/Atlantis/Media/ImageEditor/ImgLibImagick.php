<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Media\ImageEditor;

use Nether\Common;

use Imagick;
use Exception;

################################################################################
################################################################################

class ImgLibImagick
extends ImgLib {

	protected Imagick
	$Img;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
			$this->Img->ResetIterator();
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
		$this->Img->Destroy();

		return $this;
	}

	public function
	Save(string $Filename):
	static {

		$this->Img->WriteImage($Filename);

		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	IsOpen():
	bool {

		return isset($this->Img);
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

	public function
	RemoveMetadata():
	static {

		$this->Img->StripImage();

		return $this;
	}

};
