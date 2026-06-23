<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;

use Throwable;

################################################################################
################################################################################

class DeckManagerAPI
extends Atlantis\ProtectedAPI {

	public function
	OnReady(?Common\Datastore $ExtraData):
	void {

		parent::OnReady($ExtraData);

		($this->Input)
		->SetFilters('Name', Common\Filters\Text::TrimmedNullable(...))
		->SetFilters('Alias', Common\Filters\Text::TrimmedNullable(...));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Atlantis\Meta\RouteHandler('/ops/deckmgr/api', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityPost():
	void {

		$Name = $this->Input->Get('Name');
		$Alias = $this->Input->Get('Alias');
		$Err = NULL;

		////////

		if(!$Name)
		$this->Quit(1, 'no Name specified');

		if(!$Alias)
		$this->Quit(2, 'no Alias specified');

		////////

		try {
			$Deck = Atlantis\UI\SlideDeck::Create(
				$this->App,
				$Name,
				$Alias
			);
		}

		catch(Throwable $Err) {
			$this->Quit(3, $Err->GetMessage());
		}

		($this)
		->SetGoto(sprintf('/ops/deckmgr/%s', $Deck->GetAlias()))
		->SetPayload($Deck->DescribeForPublicAPI());

		return;
	}

	#[Atlantis\Meta\RouteHandler('/ops/deckmgr/api', Verb: 'DELETE')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	EntityDelete():
	void {

		$Alias = $this->Input->Get('Alias');
		$Deck = Atlantis\UI\SlideDeck::Load($this->App, $Alias);

		if($Deck)
		unlink($Deck->Filename);

		return;
	}

};
