<?php

namespace Nether\Atlantis\Packages\DevTool;

use Nether\Atlantis;
use Nether\Common;
use Nether\Console;

trait QuerySelectProjectConfig {

	#[Common\Meta\Date('2023-11-09')]
	#[Common\Meta\Info('Prompt the user to select from the supplied config files.')]
	public function
	QuerySelectProjectConfig(callable $Filter=NULL):
	?Atlantis\Struct\ProjectJSON {

		$Confs = Atlantis\Struct\ProjectJSON::FromApp($this->App);
		$Output = NULL;

		if(is_callable($Filter))
		$Confs->Filter($Filter);

		if($Confs->Count() === 0) {
			$this->PrintStatusMuted("No configs found.");
			return NULL;
		}

		if($Confs->Count() === 1) {
			$Output = $Confs->Revalue()[0];
			$this->PrintStatusMuted("Selected: {$Output->Filename}");

			return $Output;
		}

		////////

		$MakeConfList = function(Common\Datastore $Confs) {
			$Out = new Common\Datastore;

			$Confs->Each(
				fn(Atlantis\Struct\ProjectJSON $P, string $Key)
				=> $Out->Push(sprintf(
					'%s %s',
					$Key,
					$this->Format("({$P->Filename})", Console\Theme::Muted)
				))
			);

			return $Out;
		};

		$this->PrintLn($this->FormatHeaderPoint('Atlantis ProjectJSON Files:', Console\Theme::Accent), 2);
		$this->PrintLn($this->FormatBulletList($MakeConfList($Confs)), 2);

		////////

		$Key = $this->PromptForValue(
			'Select File', 'Number', TRUE,
			Common\Filters\Numbers::IntType(...)
		);

		////////

		$Output = $Confs->Values()->Get($Key - 1);

		if($Output)
		$this->PrintStatus("Selected: {$Output->Filename}");
		else
		$this->PrintStatus("No Config Selected");

		return $Output;
	}

};
