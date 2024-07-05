<?php

namespace Nether\Atlantis\Packages;

use Nether\Atlantis;
use Nether\Database;
use Nether\Common;

#[Common\Meta\Date('2023-08-20')]
trait CoverImage {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: NULL)]
	#[Database\Meta\ForeignKey('Uploads', 'ID')]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntNullable' ])]
	#[Common\Meta\PropertyListable]
	public ?int
	$CoverImageID;

	#[Database\Meta\TableJoin('CoverImageID')]
	public Atlantis\Media\File
	$CoverImage;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasCoverImage():
	bool {

		if(isset($this->CoverImage))
		return TRUE;

		return FALSE;
	}

	public function
	GetCoverImageURL(string $Size='lg'):
	?string {

		if(!$this->HasCoverImage())
		return NULL;

		////////

		$URL = str_replace(
			'original.', "{$Size}.",
			$this->ImageHeader->GetPublicURL()
		);

		if(isset($this->CoverImage->ExtraFiles))
		$URL = $this->GetCoverImageSizeURL($Size, $URL);

		return (string)Atlantis\WebURL::FromString($URL);
	}

	protected function
	GetCoverImageSizeURL(string $Size, string $URL):
	?string {

		$FName = NULL;
		$FInfo = NULL;

		foreach($this->CoverImage->ExtraFiles as $FName => $FInfo) {

			// if we're not looking at something we sized.

			if(!str_contains($FName, '.'))
			continue;

			// if(md.jpeg begins with md)

			if(str_starts_with($FName, $Size)) {
				$URL = str_replace(basename($URL), $FName, $URL);
				break;
			}

		}

		return $URL;
	}

}
