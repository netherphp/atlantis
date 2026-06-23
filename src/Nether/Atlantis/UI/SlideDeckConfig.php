<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class SlideDeckConfig
extends Common\Prototype {

	public string
	$Filename;

	public string
	$Name;

	public string
	$Ratiobox = 'widescreen';

	public array|Common\Datastore
	$Items = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		$this->Items = Common\Datastore::FromArray($this->Items);
		$this->Items->Remap(fn(mixed $Data)=> new SlideDeckItem($Data));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Write():
	void {

		$Data = json_encode([
			'Name'     => $this->Name,
			'Ratiobox' => $this->Ratiobox,
			'Items'    => $this->Items->Export()
		]);

		Common\Filesystem\Util::TryToWriteFile($this->Filename, $Data);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $Filename):
	static {

		$Data = Common\Filesystem\Util::TryToReadFileJSON($Filename);

		$Output = new static($Data);
		$Output->Filename = $Filename;

		return $Output;
	}

};
