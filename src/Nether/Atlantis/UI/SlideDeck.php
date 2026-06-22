<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\UI;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

################################################################################
################################################################################

class SlideDeck
extends Surface\Element {

	protected Atlantis\Engine
	$App;

	public string
	$Area = 'elements/slidedeck/fader';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public string
	$Ratiobox = 'widescreen';

	public array|Common\Datastore
	$Items;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Read(string $Name):
	mixed {

		$Path = sprintf('slidedecks/%s.json', $Name);
		$JSON = $this->App->Storage->Get('Default', $Path);
		$Data = Common\Datastore::FromJSON($JSON);

		$Items = new Common\Datastore;
		$K = NULL;
		$V = NULL;

		if($Data->HasKey('Ratiobox') && is_string($Data['Ratiobox']))
		$this->Ratiobox = $Data['Ratiobox'];

		if($Data->HasKey('Items') && is_array($Data['Items']))
		foreach($Data['Items'] as $K => $V) {
			$Items->Push(new SlideDeckItem($V));
		}

		$this->Items = $Items;

		return NULL;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Load(Atlantis\Engine $App, string $Name):
	static {

		$Output = new static($App->Surface);
		$Output->App = $App;
		$Output->Read($Name);

		return $Output;
	}

};
