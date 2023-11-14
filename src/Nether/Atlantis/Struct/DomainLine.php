<?php

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

class DomainLine
implements
	Common\Interfaces\IsConfigured,
	Common\Interfaces\ToArray,
	Common\Interfaces\ToJSON,
	Common\Interfaces\ToString {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public ?string
	$Primary;

	public ?string
	$Secondary;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Input=NULL, bool $CmdInc=FALSE) {

		if($Input !== NULL)
		$this->Parse($Input, $CmdInc);

		return;
	}

	////////////////////////////////////////////////////////////////
	// IMPLEMENTS Common Interfaces ////////////////////////////////

	use
	Common\Package\ToJSON;

	public function
	IsConfigured():
	bool {

		return $this->Primary !== NULL;
	}

	public function
	ToArray():
	array {

		return [
			'Primary'   => $this->Primary,
			'Secondary' => $this->Secondary
		];
	}

	public function
	ToString():
	string {

		if($this->Primary && $this->Secondary)
		return "{$this->Primary} {$this->Secondary}";

		if($this->Primary)
		return $this->Primary;

		return '';
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Parse(string $Input, bool $CmdInc=FALSE):
	bool {

		$this->Primary = NULL;
		$this->Secondary = NULL;

		// clean up accidental extra spaces to get a good break.

		$Input = preg_replace('#[\s{2,}]#', ' ', $Input);

		// primary.tld secondary.tld
		// cmd primary.tld secondary.tld tertiary.tld

		$Cap = $CmdInc ? 3 : 2;
		$Bits = explode(' ', $Input, $Cap);

		if(count($Bits) === $Cap) {
			$this->Primary = trim($Bits[$Cap - 2]);
			$this->Secondary = trim($Bits[$Cap - 1]);

			return TRUE;
		}

		if(count($Bits) === ($Cap - 1)) {
			$this->Primary = trim($Bits[$Cap - 2]);
			$this->Secondary = NULL;

			return TRUE;
		}

		return $this->IsConfigured();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Contains(string $Line, string $Domain):
	bool {

		$Pattern = sprintf('#\b%s\b#', preg_quote($Domain, '#'));

		return !!preg_match($Pattern, $Line);
	}

	static public function
	MergeLines(...$Lines):
	string {

		$Lines = (
			Common\Datastore::FromArray(Common\Filters\Lists::ArrayOf(
				$Lines, Common\Filters\Text::Trimmed(...)
			))
			->Map(fn(string $L)=> trim($L))
			->Filter(fn(string $L)=> !!$L)
		);

		if($Lines->Count() < 2)
		throw new Common\Error\RequiredDataMissing('Lines', 'at least two strings');

		////////

		/** @var Common\Datastore $Pile */

		$Pile = $Lines->Accumulate(new Common\Datastore,
			fn(Common\Datastore $C, string $L)
			=> $C->MergeRight(explode(' ', $L))
		);

		return (
			$Pile
			->Flatten()
			->Join(' ')
		);
	}

	static public function
	Strip(string $Line, string $Remove):
	string {

		return str_replace(" {$Remove}", '', $Line);
	}

};
