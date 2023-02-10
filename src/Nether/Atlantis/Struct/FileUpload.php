<?php

namespace Nether\Atlantis\Struct;

use Nether\Common;
use Nether\Database;
use Nether\Storage;

#[Database\Meta\TableClass('Uploads', 'UP')]
#[Database\Meta\InsertUpdate]
#[Database\Meta\InsertReuseUnique]
class FileUpload
extends Database\Prototype {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE, Nullable: FALSE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
	#[Database\Meta\FieldIndex(Unique: TRUE)]
	public string
	$UUID;

	#[Database\Meta\TypeVarChar(Size: 128, Nullable: FALSE)]
	public string
	$Name;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0, Nullable: FALSE)]
	public int
	$Size;

	#[Database\Meta\TypeVarChar(Size: 8, Default: 'file', Nullable: FALSE)]
	public string
	$Type;

	#[Database\Meta\TypeVarChar(Size: 255, Nullable: FALSE)]
	public string
	$URL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////


}
