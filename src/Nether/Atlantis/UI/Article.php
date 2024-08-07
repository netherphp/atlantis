<?php

namespace Nether\Atlantis\UI;

use Nether\Surface;

class Article
extends Surface\Element {

	public string
	$Area = 'elements/article/main';

	public ?string
	$Class = NULL;

	public ?string
	$Title = NULL;

	public ?string
	$TitleClass = NULL;

	public ?string
	$TitleURL = NULL;

	public ?string
	$Date = NULL;

	public ?string
	$DateClass = NULL;

	public ?string
	$Authour = NULL;

	public ?string
	$AuthourClass = NULL;

	public ?string
	$AuthourURL = NULL;

	public string|Surface\Element|NULL
	$Section = NULL;

	public ?string
	$SectionClass = NULL;

	public ?string
	$SectionURL = NULL;

	public Surface\Element|string|NULL
	$Content = NULL;

	public ?string
	$ContentClass = NULL;

}
