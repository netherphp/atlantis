<?php

namespace Nether\Atlantis\UI;

use Nether\Surface;

class PromoVideo
extends Surface\Element {

	public string
	$Area = 'elements/promo-video/main';

	public ?string
	$Title = NULL;

	public ?string
	$Subline = NULL;

	public ?string
	$Overlay = NULL;

	public ?string
	$VideoURL = NULL;

	public bool
	$Typeify = FALSE;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromSurfaceWith(Surface\Engine $Surface, iterable $Opts):
	static {

		$Output = static::FromSurface($Surface);
		$Key = NULL;
		$Val = NULL;

		foreach($Opts as $Key => $Val)
		if(property_exists($Output, $Key))
		$Output->{$Key} = $Val;

		return $Output;
	}

}
