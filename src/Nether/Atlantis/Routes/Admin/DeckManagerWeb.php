<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class DeckManagerWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/deckmgr')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	Index():
	void {

		$List = Atlantis\UI\SlideDeck::List($this->App);

		($this)
		->SetPageTitle('Slide Decks // Operations')
		->Area('admin/slidedecks/index', [
			'List' => $List
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/ops/deckmgr/:Alias:')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	Edit(string $Alias):
	void {

		$Deck = Atlantis\UI\SlideDeck::Load($this->App, $Alias);

		($this)
		->SetPageTitle(sprintf('%s // Slide Decks // Operations', $Deck->GetName()))
		->Area('admin/slidedecks/edit', [
			'Deck' => $Deck
		]);

		return;
	}

};
