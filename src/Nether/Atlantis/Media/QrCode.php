<?php

namespace Nether\Atlantis\Media;

use BaconQrCode;
use Nether\Atlantis;
use Nether\Common;

use Stringable;

class QrCode
implements Stringable {

	public string
	$URL;

	public int
	$Size = 1200;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $URL, int $Size=1200) {
		$this->URL = $URL;
		$this->Size = $Size;

		$FilePath = $this->GetFilePath();

		if(!file_exists($FilePath)) {
			if(!file_exists(dirname($FilePath)))
			Common\Filesystem\Util::MkDir(dirname($FilePath));

			$QrData = $this->Generate();
			file_put_contents($FilePath,$QrData);
		}

		return;
	}

	public function
	__ToString():
	string {
	/*//
	@date 2021-02-01
	//*/

		return $this->GetURL();
	}

	public function
	GetURL():
	string {
	/*//
	@date 2021-02-01
	//*/

		return new Atlantis\WebURL($this->GetFileURI());
	}

	public function
	GetFileURI():
	string {
	/*//
	returns the site root relative uri
	@date 2020-09-01
	//*/

		return sprintf(
			'/data/qr/url-%s.png',
			md5($this->URL)
		);
	}

	public function
	GetFilePath():
	string {
	/*//
	returns the site root relative uri
	@date 2020-09-01
	//*/

		return sprintf(
			'%s/data/qr/url-%s.png',
			ProjectRoot,
			md5($this->URL)
		);
	}

	public function
	Generate():
	string {
	/*//
	@date 2020-09-01
	//*/

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

}
