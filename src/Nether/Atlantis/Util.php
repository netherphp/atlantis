<?php

namespace Nether\Atlantis;

use ReCaptcha;
use Nether\Atlantis;
use Nether\Common;

use FilesystemIterator;
use Generator;
use Exception;
use Imagick;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class Util
implements Atlantis\Plugin\Interfaces\Engine\AppInstanceStaticInterface {

	use
	Atlantis\Packages\AppInstanceStatic;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetSelectedHTML(bool $Cond):
	string {

		if($Cond)
		return 'selected="selected"';

		return '';
	}

	static public function
	GetCheckedHTML(bool $Cond):
	string {

		if($Cond)
		return 'checked="checked"';

		return '';
	}

	static public function
	PrintHTML(?string $Input):
	void {

		if($Input === NULL)
		$Input = '';

		echo Filter::EncodeHTML($Input);
		return;
	}

	static public function
	Copy(string $Source, string $Dest):
	bool {

		// try to avoid breaking on windows with this one easy trick

		$Source = static::Repath($Source);
		$Dest = static::Repath($Dest);

		// if we're talking about a file then just copy it and be done.

		if(is_file($Source)) {
			copy($Source, $Dest);
			return file_exists($Dest);
		}

		// if we are talking about a directory then recursively dive it.

		Util::MkDir($Dest);
		$File = NULL;

		$Iter = new FilesystemIterator($Source, (
			FilesystemIterator::CURRENT_AS_FILEINFO |
			FilesystemIterator::SKIP_DOTS
		));

		foreach($Iter as $File) {
			$Suffix = str_replace(
				"{$Source}/", '',
				str_replace('\\', '/', $File->GetPathname())
			);

			Util::Copy($File, "{$Dest}/{$Suffix}");
		}

		return file_exists($Dest);
	}

	static public function
	CopyWithConfirm(string $Source, string $Dest, bool $Force=FALSE):
	Generator {

		$Source = static::Repath($Source);
		$Dest = static::Repath($Dest);
		$Keep = NULL;

		if(!file_exists($Source))
		return FALSE;

		if(is_file($Source)) {
			if($Force)
			$Keep = FALSE;
			else
			$Keep = file_exists($Dest) ? yield $Dest : FALSE;

			if(!$Keep)
			copy($Source, $Dest);

			return file_exists($Dest);
		}

		////////

		if(!static::MkDir($Dest))
		return FALSE;

		$Iter = new FilesystemIterator($Source, (0
			| FilesystemIterator::CURRENT_AS_FILEINFO
			| FilesystemIterator::SKIP_DOTS
		));

		$FileInfo = NULL;
		$Path = NULL;
		$Final = NULL;
		$Result = NULL;

		foreach($Iter as $FileInfo) {
			$Path = static::Repath($FileInfo->GetPathname());
			$Final = str_replace("{$Source}/", "{$Dest}/", $Path);

			foreach(static::CopyWithConfirm($Path, $Final) as $Result)
			if(!$Force)
			yield $Result;
		}

		return file_exists($Dest);
	}

	static public function
	MkDir(string $Path):
	bool {

		$Path = str_replace('\\', '/', $Path);

		if(!file_exists($Path)) {
			$UMask = umask(0);
			mkdir($Path, 0777, TRUE);
			umask($UMask);
		}

		return is_dir($Path);
	}

	static public function
	Repath(string $Input):
	string {

		return str_replace('\\', '/', $Input);
	}

	static public function
	AppendGoto(string $URL, string $What):
	string {

		$Encoded = Common\Filters\Text::Base64Encode($What);
		$Output = $URL;

		if(str_contains($URL, '?'))
		$Output .= "&goto={$Encoded}";
		else
		$Output .= "?goto={$Encoded}";

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DomainFromURL(?string $URL):
	string {

		$URL ??= '';
		$Domain = parse_url($URL, PHP_URL_HOST);

		// parse_url can return a lot of different results. for this
		// purpose anything not a string is an error condition.

		if(!is_string($Domain))
		return 'Unknown';

		// strip off literal www from the start of domain names.

		if(str_starts_with($Domain, 'www.'))
		$Domain = preg_replace('/^www\./', '', $Domain);

		return $Domain ?: '';
	}

	static public function
	IfOneOrMore(int $Count, mixed $Singular, mixed $Plural):
	mixed {

		return match($Count) {
			1       => $Singular,
			default => $Plural
		};
	}

	static public function
	IsReCaptchaValid(Atlantis\Engine $App):
	bool {

		$ApiEnabled = $App->Config['Google.ReCaptcha.Enabled'];
		$ApiKey = $App->Config['Google.ReCaptcha.PrivateKey'];

		if(!$ApiEnabled)
		return TRUE;

		if($ApiEnabled && !$ApiKey)
		throw new Exception('Missing Google.ReCaptcha.PrivateKey in config.');

		////////

		$Response = (
			$App->Router->Request->Data
			->Get('g-recaptcha-response')
		);

		$RemoteAddr = (
			isset($_SERVER['REMOTE_ADDR'])
			? $_SERVER['REMOTE_ADDR']
			: NULL
		);

		////////

		$ReCaptcha = new ReCaptcha\ReCaptcha($ApiKey);
		$ReResult = $ReCaptcha->Verify($Response, $RemoteAddr);

		return (Bool)$ReResult->IsSuccess();
	}

	static public function
	TrimHTML(?string $Input, bool $Nullable=FALSE):
	?string {

		$Output = $Input ?? '';

		// if the content is short, strip and trim to find out if it ends
		// up looking like a bunch of nothing.

		if(isset($Output) && strlen($Output) < 64)
		if(trim(strip_tags($Output)) === '')
		$Output = '';

		// then plain trim the content as well.

		$Output = trim($Output);

		// if we want it nullable and it seems falsy.

		if($Nullable && !$Output)
		$Output = NULL;

		return $Output;
	}

	static public function
	GetBinProjectDirectory(string $Origin=NULL):
	string {

		$Origin ??= __FILE__;
		$BinPath = Atlantis\Util::Repath(dirname($Origin));
		$CurPath = Atlantis\Util::Repath(getcwd());

		// if it looks like we in a project directory assume we are in
		// the project directory.

		if(file_exists(sprintf('%s/composer.lock', $CurPath)))
		if(str_starts_with($BinPath, $CurPath))
		return $CurPath;

		// if we are elsewhere but calling this installed as a vendor
		// binary assume that the project directory is up from that.

		if(str_ends_with($BinPath, 'vendor/netherphp/atlantis/bin'))
		return dirname(__FILE__, 7);

		// else just yolo with the current path again.

		return $CurPath;
	}

	static public function
	RewriteFileExtension(string $Filename, string $ExtOld, string $ExtNew):
	string {

		$Output = $Filename;

		////////

		// if the old file was extensionless then just append an extension
		// to the end of it.

		if(!$ExtOld)
		return sprintf('%s.%s', $Output, $ExtNew);

		// else replace the old extension with the new extension.

		$Output = preg_replace(
			sprintf('#%s$#', preg_quote($ExtOld, '#')),
			$ExtNew,
			$Output
		);

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	IsFormatSupported(string $What):
	bool {

		if(static::IsFormatSupportedByImagick($What))
		return TRUE;

		if(static::IsFormatSupportedByGD($What))
		return TRUE;

		return FALSE;
	}

	static public function
	IsFormatSupportedByImagick(string $What):
	bool {

		return in_array(
			strtoupper($What),
			Imagick::QueryFormats()
		);
	}

	static public function
	IsFormatSupportedByGD(string $What):
	bool {

		$Fmts = gd_info();
		$What = strtoupper($What);

		$Keys = NULL;
		$Claim = NULL;
		$Buff = NULL;

		////////

		// if GD clearly does not know what it is...

		$Keys = new Common\Datastore([
			sprintf('%s Support', $What),
			sprintf('%s Read Support', $What)
		]);

		$Claim = $Keys->Accumulate(FALSE, (
			fn(bool $Prev, string $K)
			=> ((isset($Fmts[$K]) && $Fmts[$K]) ?: $Prev)
		));

		if(!$Claim)
		return FALSE;

		////////

		// problem #1 - PHP-GD lies about the support on the high level
		// with its configuration values. they are stating if it was built
		// with the apis enabled only. it is possible to get this far but
		// not actually support what it said it does.

		$ImageWriteFunc = match($What) {
			'AVIF'  => 'imageavif',
			'BMP'   => 'imagebmp',
			'GIF'   => 'imagegif',
			'JPG'   => 'imagejpeg',
			'JPEG'  => 'imagejpeg',
			'PNG'   => 'imagepng',
			default => NULL
		};

		if($ImageWriteFunc === NULL || !function_exists($ImageWriteFunc))
		return FALSE;

		////////

		// problem #2 - image[avif|jpeg|png] will report a success while
		// spamming old school warnings to the logs regarding failure. so
		// the only way to truely know is to actually write something
		// and see if it is ok.

		$Buff = new Common\Overbuffer;

		$Claim = $Buff->Exec(function() use($ImageWriteFunc) {
			$GD = imagecreate(1, 1);

			$Lie = @$ImageWriteFunc($GD, NULL);
			imagedestroy($GD);

			return $Lie;
		});

		// if it says it failed then we accept that.

		if(!$Claim)
		return FALSE;

		// but now we have to call its bluff and check for some
		// known failure states.

		if($Buff->Length() === 0)
		return FALSE;

		////////

		return TRUE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DomainToBrandName(string $Domain):
	string {

		return match($Domain) {
			't.co'          => 'Twitter',
			'facebook.com'  => 'Facebook',
			'instagram.com' => 'Instagram',
			'apple.com'     => 'Apple Log In',
			'google.com'    => 'Google',
			default         => $Domain
		};
	}

	#[Common\Meta\Info('Fetch the site tags for the current site.')]
	static public function
	FetchSiteTags():
	Common\Datastore {

		$Output = new Common\Datastore;
		$Tags = Library::Get(Key::ConfSiteTags);
		$Item = NULL;

		// empty setting bail.

		if(!$Tags)
		return $Output;

		// single tag reformat.

		if(is_int($Tags) || is_string($Tags))
		$Tags = [ $Tags ];

		// loop through tags.

		foreach($Tags as $Item) {
			$Tag = NULL;

			if(is_int($Item))
			$Tag = Atlantis\Tag\Entity::GetByID($Item);

			if(is_string($Item))
			$Tag = Atlantis\Tag\Entity::GetByField('Alias', $Item);

			if($Tag instanceof Atlantis\Tag\Entity)
			$Output->Push($Tag);
		}

		return $Output;
	}

	#[Common\Meta\Info('Fetch all of the site tags that exist.')]
	static public function
	FetchSiteTagsAll():
	Common\Datastore {

		$Output = Atlantis\Tag\Entity::Find([
			'Type'  => 'site',
			'Sort'  => 'title-az',
			'Limit' => 0
		]);

		return $Output;
	}

	static public function
	FetchStatesFromJSON(Atlantis\Engine $App):
	Common\Datastore {

		$Output = Common\Datastore::FromFile($App->FromProjectRoot(
			'www/share/atlantis/data/us-states.json'
		));

		return $Output;
	}

	static public function
	RewriteURL(string $URL='', ?Common\Datastore $Tags=NULL, ?Atlantis\Engine $App=NULL):
	string {

		// i seriously need to work out a way for libraries to boil this
		// down better. this is depending on the web route having made the
		// global instance to just reach up and pull out of nowhere.

		$App ??= static::$AppInstance;

		// try to select one of the site tags from the input tags that is
		// also a valid site tag for the domain we are sitting on.

		if(str_starts_with($URL, '/') && $Tags && $Tags->Count()) {
			$CTags = $App->Config[Atlantis\Key::ConfSiteTags] ?: [];
			$STags = $Tags->Distill(fn(Atlantis\Tag\Entity $T)=> in_array($T->Alias, $CTags));

			if($STags->Count()) {
				$STag = $STags->Current();

				if($STag->ExtraData->HasKey('URL'))
				$URL = sprintf('%s%s', $STags->Current()->ExtraData['URL'], $URL);
			}

			unset($CTags, $STags, $STag);
		}

		// if no matching site tags were found use the first site tag
		// the site is currently using.

		if(str_starts_with($URL, '/') && $Tags && $Tags->Count()) {
			$CTags = $App->Config[Atlantis\Key::ConfSiteTags];
			$STags = $Tags->Distill(fn(Atlantis\Tag\Entity $T)=> $T->Type === 'site');

			if($STags->Count()) {
				$STag = $STags->Current();

				if($STag->ExtraData->HasKey('URL'))
				$URL = sprintf('%s%s', $STag->ExtraData['URL'], $URL);
			}

			unset($CTags, $STags, $STag);
		}

		// transform the atl:// prefix into a full url. mainly for
		// spitting out environment specific links.

		if(str_starts_with($URL, 'atl://')) {
			$URL = match(TRUE) {
				$App->IsDev()
				=> preg_replace('#^atl://(?:www\.)?#', 'https://dev.', $URL),

				default
				=> preg_replace('#^atl://#', 'https://', $URL)
			};
		}

		return $URL;
	}

}
