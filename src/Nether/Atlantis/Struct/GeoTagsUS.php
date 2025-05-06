<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Struct;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class GeoTagsUS {

	static public function
	FetchJSON(Atlantis\Engine $App):
	Common\Datastore {

		$Filename = 'www/share/atlantis/data/us-states.json';
		$Data = $App->FromProjectRoot($Filename);
		$States = Common\Datastore::FromFile($Data);

		return $States;
	}

	static public function
	FetchTags(Atlantis\Engine $App):
	Common\Datastore {

		$States = static::FetchJSON($App);

		$Tags = Atlantis\Tag\Entity::Find([ 'Alias' => $States->Values()->Export() ]);
		$Tags->RemapKeys(fn(int $K, Atlantis\Tag\Entity $T)=> [ $T->Name=> $T ]);

		$States->RemapKeyValue(fn(mixed $K, string $V)=> $Tags[$V]);
		$States->Filter(fn(mixed $V)=> $V !== NULL);

		return $States;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	PopulateTagsByAbbr(Atlantis\Engine $App):
	Common\Datastore {

		$States = static::FetchJSON($App);
		$Tag = NULL;

		foreach($States as $SK => $SN) {
			$Tag = Atlantis\Tag\Entity::Touch(
				Alias: sprintf('us-%s', strtolower($SK)),
				Name: $SN
			);

			$States[$SK] = $Tag;
		}

		return $States;
	}

	static public function
	PopulateTagsByName(Atlantis\Engine $App):
	Common\Datastore {

		$States = static::FetchJSON($App);
		$Tag = NULL;

		foreach($States as $SK => $SN) {
			$Tag = Atlantis\Tag\Entity::Touch(
				Alias: Common\Filters\Text::SlottableKey($SN),
				Name: $SN
			);

			$States[$SK] = $Tag;
		}

		return $States;
	}

};
