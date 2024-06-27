<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

#[Database\Meta\TableClass('TrafficReportDay', 'TRD')]
class TrafficReportDay
extends Atlantis\Prototype {

	#[Database\Meta\TypeDate]
	#[Database\Meta\FieldIndex]
	public string
	$Date;

	#[Database\Meta\TypeChar(Size: 100)]
	#[Database\Meta\FieldIndex]
	public string
	$Domain;

	#[Database\Meta\TypeChar(Size: 255)]
	#[Database\Meta\FieldIndex]
	public string
	$Path;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$Hits;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$Visitors;

};
