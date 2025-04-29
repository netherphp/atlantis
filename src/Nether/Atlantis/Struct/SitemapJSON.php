<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class SitemapJSON {

	public SitemapLink
	$Home;

	public SitemapLink
	$Contact;

	public array|Common\Datastore
	$Main;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(array $Input) {

		$this->FillHome($Input);
		$this->FillContact($Input);
		$this->FillMain($Input);

		return;
	}

	public function
	FillHome(array $Input):
	void {

		if(!array_key_exists('Home', $Input))
		return;

		if(!is_array($Input['Home']))
		return;

		$this->Home = new SitemapLink($Input['Home']);

		return;
	}

	public function
	FillContact(array $Input):
	void {

		if(!array_key_exists('Contact', $Input))
		return;

		if(!is_array($Input['Contact']))
		return;

		$this->Contact = new SitemapLink($Input['Contact']);

		return;
	}

	public function
	FillMain(array $Input):
	void {

		if(!array_key_exists('Main', $Input))
		return;

		if(!is_array($Input['Main']))
		return;

		$this->Main = new Common\Datastore($Input['Main']);
		$this->Main->Remap(fn(array $V)=> new SitemapLink($V));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $Filename):
	static {

		$Output = new static(Common\Filesystem\Util::TryToReadFileJSON($Filename));

		return $Output;
	}

};
