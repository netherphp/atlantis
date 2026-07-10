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
	$Filename;

	public SlideDeckConfig
	$Config;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAlias():
	string {

		$Bits = Common\Datastore::FromString(
			$this->Config->Filename, DIRECTORY_SEPARATOR
		);

		$Alias = str_replace(
			'.json', '',
			$Bits[$Bits->GetLastKey()]
		);

		return $Alias;
	}

	public function
	GetName():
	string {

		return $this->Config->Name;
	}

	public function
	GetRatiobox():
	string {

		return $this->Config->Ratiobox;
	}

	public function
	GetItems():
	Common\Datastore {

		return $this->Config->Items->Copy();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	DescribeForPublicAPI():
	array {

		$Output = [
			'Name' => $this->GetName(),
			'Alias' => $this->GetAlias()
		];

		return $Output;
	}

	public function
	Read(string $Alias):
	void {

		$this->Filename = static::Filename($this->App, $Alias);
		$this->Config = SlideDeckConfig::FromFile($this->Filename);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Filename(Atlantis\Engine $App, string $Alias):
	string {

		$Output = match(TRUE) {
			(str_contains($Alias, '.json')),
			=> $Alias,

			default
			=> $App->FromProjectRoot(sprintf(
				'data/slidedecks/%s.json', $Alias
			))
		};

		return $Output;
	}

	static public function
	Create(Atlantis\Engine $App, string $Name, string $Alias):
	static {

		$Filename = static::Filename($App, $Alias);

		if(file_exists($Filename))
		throw new Common\Error\FileExists($Alias);

		////////

		try {
			$Config = new SlideDeckConfig;
			$Config->Filename = $Filename;
			$Config->Name = $Name;
			$Config->Write();
		}

		catch(\Exception $Err) {
			throw $Err;
		}

		////////

		$Deck = static::Load($App, $Alias);

		return $Deck;
	}

	static public function
	Load(Atlantis\Engine $App, string $Alias):
	static {

		$Output = new static($App->Surface);
		$Output->App = $App;
		$Output->Read($Alias);

		return $Output;
	}

	static public function
	List(Atlantis\Engine $App):
	Common\Datastore {

		$Path = $App->FromProjectRoot('data/slidedecks');

		if(!file_exists($Path))
		Common\Filesystem\Util::MkDir($Path);

		////////

		$Index = Common\Filesystem\Indexer::DatastoreFromPath($Path);

		$Index->Remap(
			fn(string $Filename)
			=> static::Load($App, $Filename)
		);

		$Index->Sort(
			fn(SlideDeck $A, SlideDeck $B)
			=> $A->GetName() <=> $B->GetName()
		);

		return $Index;
	}

};
