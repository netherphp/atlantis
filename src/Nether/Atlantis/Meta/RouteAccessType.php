<?php

namespace Nether\Atlantis\Meta;
use Nether;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class RouteAccessType {

	const
	Equals    = 'EQ',
	Greater   = 'GT',
	GreaterEq = 'GTE',
	Lesser    = 'LT',
	LesserEq  = 'LTE',
	Has       = 'HAS',
	Not       = 'NOT';

	public string
	$Key;

	public string
	$Type;

	public int
	$Value;

	public function
	__Construct(string $Key, string $Type='eq', int $Value=1) {

		$this->Key = $Key;
		$this->Type = strtoupper($Type);
		$this->Value = $Value;

		return;
	}


	public function
	WillAccept(mixed $Val):
	bool {

		//Nether\Avenue\Util::VarDumpPre($this);
		//Nether\Avenue\Util::VarDumpPre($Req);

		return match($this->Type) {
			self::Equals    => ($Val === $this->Value),
			self::Greater   => ($Val > $this->Value),
			self::GreaterEq => ($Val >= $this->Value),
			self::Lesser    => ($Val < $this->Value),
			self::LesserEq  => ($Val <= $this->Value),
			self::Has       => TRUE,
			self::Not       => FALSE,
			default             => FALSE
		};
	}

}
