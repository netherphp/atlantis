<?php

namespace Nether\Atlantis\Media;

use BaconQrCode;
use Nether\Atlantis;
use Nether\Common;

use Stringable;

class QrCode
implements Stringable {

	public string
	$BaseDir;

	public string
	$URL;

	public int
	$Size = 1200;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $URL, int $Size=1200, bool $AutoGen=TRUE) {

		$this->BaseDir = ProjectRoot;
		$this->URL = $URL;
		$this->Size = $Size;

		$FilePath = $this->GetFilePath();

		////////

		if($AutoGen)
		$this->Write($this->Generate());

		////////

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2021-02-01')]
	public function
	__ToString():
	string {

		return $this->GetURL();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2021-02-01')]
	public function
	GetURL():
	string {

		return new Atlantis\WebURL($this->GetFileURI());
	}

	#[Common\Meta\Date('2020-09-01')]
	#[Common\Meta\Info('returns the site root relative uri.')]
	public function
	GetFileURI():
	string {

		return sprintf(
			'/data/qr/qr-%s.png',
			$this->GetContentHash()
		);
	}

	#[Common\Meta\Date('2020-09-01')]
	#[Common\Meta\Info('returns the local file path.')]
	public function
	GetFilePath():
	string {

		return sprintf(
			'%s/data/qr/qr-%s.png',
			$this->BaseDir,
			$this->GetContentHash()
		);
	}

	#[Common\Meta\Date('2023-12-10')]
	public function
	GetContentHash():
	string {

		return base_convert(
			hash('sha256', $this->URL),
			16, 36
		);
	}

	#[Common\Meta\Date('2020-09-01')]
	public function
	Generate():
	string {

		$QrRender = NULL;
		$QrWriter = NULL;
		$QrData = NULL;

		////////

		$QrRender = new BaconQrCode\Renderer\ImageRenderer(
			new BaconQrCode\Renderer\RendererStyle\RendererStyle($this->Size),
			new BaconQrCode\Renderer\Image\ImagickImageBackEnd
		);

		$QrWriter = new BaconQrCode\Writer($QrRender);
		$QrData = $QrWriter->writeString($this->URL);

		return $QrData;
	}

	public function
	Write(?string $Data=NULL):
	static {

		$Filepath = $this->GetFilePath();

		if($Data === NULL)
		$Data = $this->Generate();

		////////

		if(!file_exists(dirname($Filepath)))
		Common\Filesystem\Util::MkDir(dirname($Filepath));

		if(!is_writable(dirname($Filepath)))
		throw new Common\Error\DirUnwritable($Filepath);

		if(file_exists($Filepath) && !is_writable($Filepath))
		throw new Common\Error\FileUnwritable($Filepath);

		file_put_contents($Filepath, $Data);

		////////

		return $this;
	}

}
