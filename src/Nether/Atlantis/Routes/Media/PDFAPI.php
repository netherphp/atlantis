<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Media;

use Nether\Atlantis;
use Nether\Common;
use Nether\Console;

use Imagick;

################################################################################
################################################################################

class PDFAPI
extends Atlantis\ProtectedAPI {

	// directly effects the quality of the pdf when its loaded. higher
	// values will make crisper fonts but take more time and ram.

	static
	$Resolution = 144;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/api/media/v1/pdf/:UUID:')]
	public function
	HandleGet(string $UUID):
	void {

		// big documents take time but if we bust that time its likely
		// apache or the browser is still going to give up anyway.

		set_time_limit(0);

		////////

		$MaxFilesize = pow(Common\Values::BitsPerUnit, 2) * 10; // 10mb

		($this->Data)
		->FilterPush('Force', Common\Filters\Numbers::BoolNullable(...));

		////////

		$File = Atlantis\Media\File::GetByUUID($UUID);
		$Path = $this->App->FromProjectRoot(str_replace(
			'storage://Default', 'data',
			$File->URL
		));

		if(!file_exists($Path))
		$this->Quit(1, 'File Not Found');

		////////

		if(filesize($Path) > $MaxFilesize)
		$this->Quit(2, 'File To Large');

		////////

		$Mime = Common\Filesystem\Util::MimeType($Path);
		$Force = $this->IsUserAdmin() && $this->Data->Get('Force');

		if($Mime !== 'application/pdf')
		$this->Quit(3, 'File Not PDF');

		////////

		$Timer = new Common\Timer;
		$Timer->Start();
		$Pages = $this->GetPagesByPage($Path);
		$Pages->Remap(fn(string $P)=> sprintf('%s/pages/%s', dirname($File->GetPublicURL()), $P));
		$Timer->Stop();

		////////

		$this->SetPayload([
			'Timer'       => $Timer->Get(),
			'DownloadURL' => $File->GetPublicURL(),
			'Pages'       => $Pages->Export()
		]);

		return;
	}

	protected function
	GetPagesOneShot(string $Filepath, bool $Force=FALSE):
	Common\Datastore {

		// load the entire pdf and then iterate over each page.
		// in testing on the current system this is somehow 25% slower than
		// loading each page one at a time.

		$Output = new Common\Datastore;
		$Dir = dirname($Filepath);
		$PDF = NULL;
		$Page = NULL;

		$PageDir = Common\Filesystem\Util::Pathify($Dir, 'pages');
		$PageCount = $this->GetPageCount($Filepath);
		$PageName = NULL;
		$PageOutput = NULL;
		$PageCur = 0;

		if(!$PageCount)
		throw new Common\Error\RequiredDataMissing('Page Count', 'int');

		////////

		$PDF = new Imagick;
		$PDF->SetAntiAlias(TRUE);
		$PDF->SetResolution(static::$Resolution, static::$Resolution);
		$PDF->SetColorspace(Imagick::COLORSPACE_SRGB);
		$PDF->ReadImage($Filepath);

		////////

		foreach($PDF as $Page) {
			$PageCur = $Page->GetIteratorIndex();
			$PageName = sprintf('%08s.jpg', ($PageCur + 1));
			$PageOutput = Common\Filesystem\Util::Pathify($PageDir, $PageName);

			if(!file_exists($PageOutput) || $Force) {
				$Page->TransformImageColorSpace(Imagick::COLORSPACE_SRGB);
				$Page->SetImageAlphaChannel(Imagick::VIRTUALPIXELMETHOD_WHITE);
				$Page->ResizeImage(1280, 1280, Imagick::FILTER_LANCZOS, 1.0, TRUE);

				$Page->SetImageCompression(Imagick::COMPRESSION_JPEG);
				$Page->SetImageCompressionQuality(97);
				$Page->SetImageFormat("jpeg");
				$Page->WriteImage($PageOutput);
			}

			$Output->Push($PageName);
		}

		$PDF->Clear();

		return $Output;
	}

	protected function
	GetPagesByPage(string $Filepath, bool $Force=FALSE):
	Common\Datastore {

		// read each page out of a pdf one at a time to not oom the machine
		// on large documents. but this also somehow ended up being faster
		// than the one shot version.

		// in the event the server killed the process for taking too long
		// it will pick up where it left off next time it tries.

		$Output = new Common\Datastore;
		$Dir = dirname($Filepath);
		$Page = NULL;

		$PageDir = Common\Filesystem\Util::Pathify($Dir, 'pages');
		$PageCount = $this->GetPageCount($Filepath);
		$PageName = NULL;
		$PageOutput = NULL;
		$PageCur = 0;

		if(!$PageCount)
		throw new Common\Error\RequiredDataMissing('Page Count', 'int');

		////////

		Common\Filesystem\Util::MkDir($PageDir);

		for($PageCur = 0; $PageCur < $PageCount; $PageCur++) {
			$PageName = sprintf('%08s.jpg', ($PageCur + 1));
			$PageOutput = Common\Filesystem\Util::Pathify($PageDir, $PageName);

			if(!file_exists($PageOutput) || $Force) {
				$Page = new Imagick;
				$Page->SetAntiAlias(TRUE);
				$Page->SetResolution(static::$Resolution, static::$Resolution);
				$Page->SetColorspace(Imagick::COLORSPACE_SRGB);

				$Page->ReadImage(sprintf('%s[%d]', $Filepath, $PageCur));
				$Page->TransformImageColorSpace(Imagick::COLORSPACE_SRGB);
				$Page->SetImageAlphaChannel(Imagick::VIRTUALPIXELMETHOD_WHITE);
				$Page->ResizeImage(1280, 1280, Imagick::FILTER_LANCZOS, 1.0, TRUE);

				$Page->SetImageCompression(Imagick::COMPRESSION_JPEG);
				$Page->SetImageCompressionQuality(97);
				$Page->SetImageFormat("jpeg");
				$Page->WriteImage($PageOutput);
				$Page->Clear();
			}

			$Output->Push($PageName);
		}

		return $Output;
	}

	protected function
	GetPageCount(string $Filename):
	int {

		$Cmd = sprintf(
			'pdfinfo %s | grep Pages',
			escapeshellarg($Filename)
		);

		$Runner = new Console\Struct\CommandLineUtil($Cmd);
		$Runner->Run();

		$Found = preg_replace(
			'/Pages:\s+/', '',
			$Runner->GetOutputString()
		);

		return (int)$Found;
	}

};
