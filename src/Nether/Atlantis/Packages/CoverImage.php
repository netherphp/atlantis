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
	GetCoverImageURL(string $Size='md'):
	?string {

		if(!$this->HasCoverImage())
		return NULL;

		$URL = $this->CoverImage->GetPublicURL();
		$URL = str_replace('original.', "{$Size}.", $URL);

		return (string)Atlantis\WebURL::FromString($URL);
	}

}
