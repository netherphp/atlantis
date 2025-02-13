<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Blob;

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;

################################################################################
################################################################################

#[Database\Meta\TableClass('AtlBlobStorage', 'AtlBS')]
class Entity
extends Atlantis\Prototype {

	const
	EntType = 'Blob.Entity';

	const
	TypeText     = 'text',
	TypeHTML     = 'html',
	TypeEditorJS = 'json-ejs';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeChar(Size: 16)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Trimmed' ])]
	public string
	$Type = self::TypeText;

	#[Database\Meta\TypeVarChar(Size: 255)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$Title = NULL;

	#[Database\Meta\TypeText]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$Content = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	DescribeForPublicAPI():
	array {
		return [
			'ID'      => $this->ID,
			'UUID'    => $this->UUID,
			'Type'    => $this->Type,
			'Title'   => $this->Title,
			'Content' => $this->GetContent()
		];
	}

	public function
	HasAnything():
	bool {

		if($this->Content === NULL)
		return FALSE;

		if(strlen($this->Content) === 0)
		return FALSE;

		////////

		return TRUE;
	}

	public function
	GetContent():
	?string {

		return $this->Content;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	TouchByUUID(string $UUID):
	static {

		$Ent = static::GetByUUID($UUID);

		if(!$Ent)
		$Ent = static::Insert([
			'UUID' => $UUID
		]);

		return $Ent;
	}

};
