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

	const
	AccessTypeManage = 'Nether.Atlantis.Blob.Entity.Manage';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('AtlBlobGroups', 'ID', Update: TRUE, Delete: NULL)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntNullable' ])]
	public int
	$GroupID;

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

	#[Database\Meta\TypeVarChar(Size: 255)]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'TrimmedNullable' ])]
	public ?string
	$ImageURL = NULL;

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
			'ID'       => $this->ID,
			'UUID'     => $this->UUID,
			'GroupID'  => $this->GroupID,
			'Type'     => $this->Type,
			'Title'    => $this->Title,
			'ImageURL' => $this->ImageURL,
			'Content'  => $this->GetContent()
		];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	HasContent():
	bool {

		if($this->Content === NULL)
		return FALSE;

		if(strlen($this->Content) === 0)
		return FALSE;

		if(strlen($this->Content) <= 16) {
			if(strip_tags($this->Content) === '')
			return FALSE;
		}



		// todo:
		// check for that annoying condition where someone empties the
		// wysiwyg but it leaves behind trace html that is technically
		// not empty despite being empty.

		////////

		return TRUE;
	}

	public function
	HasImageURL():
	bool {

		return ($this->ImageURL !== NULL);
	}

	public function
	HasTitle():
	bool {

		return ($this->Title !== NULL);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetContent():
	?string {

		if(!$this->HasContent())
		return NULL;

		return $this->Content;
	}

	public function
	GetTitle():
	?string {

		return $this->Title;
	}

	public function
	GetImageURL():
	?string {

		return $this->ImageURL;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetEditAttributes():
	Common\Datastore {

		$Output = new Common\Datastore([
			'data-atl-blob-cmd'  => 'edit',
			'data-atl-blob-uuid' => $this->UUID,
			'data-atl-blob-type' => $this->Type
		]);

		return $Output;
	}

	public function
	GetEditAttributesForHTML():
	string {

		$Attribs = $this->GetEditAttributes();

		$Attribs->RemapKeyValue(
			fn(string $K, string $V)
			=> sprintf('%s="%s"', $K, $V)
		);

		return $Attribs->Join(' ');
	}

	public function
	GetDeleteAttributes():
	Common\Datastore {

		$Output = new Common\Datastore([
			'data-atl-blob-cmd'  => 'delete',
			'data-atl-blob-uuid' => $this->UUID,
			'data-atl-blob-type' => $this->Type
		]);

		return $Output;
	}

	public function
	GetDeleteAttributesForHTML():
	string {

		$Attribs = $this->GetDeleteAttributes();

		$Attribs->RemapKeyValue(
			fn(string $K, string $V)
			=> sprintf('%s="%s"', $K, $V)
		);

		return $Attribs->Join(' ');
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input->Define([
			'GroupID' => NULL
		]);

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		if($Input['GroupID'])
		$SQL->Where('Main.GroupID=:GroupID');

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'title-az':
				$SQL->Sort('Main.Title', $SQL::SortDesc);
			break;
			case 'title-za':
				$SQL->Sort('Main.Title', $SQL::SortAsc);
			break;
		}

		return;
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

	static public function
	Touch(string $UUID, string $Type=self::TypeHTML, string $Title='', string $Content=''):
	static {

		$Ent = static::GetByUUID($UUID);

		////////

		if(!$Ent)
		$Ent = static::Insert([
			'UUID'    => $UUID,
			'Type'    => $Type,
			'Title'   => $Title,
			'Content' => $Content
		]);

		////////

		return $Ent;
	}

};
