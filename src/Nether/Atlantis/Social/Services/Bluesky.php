<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Social\Services;

use Nether\Atlantis;
use Nether\Browser;
use Nether\Common;

################################################################################
################################################################################

class Bluesky
extends Atlantis\Social\Service {

	static public string
	$ProfileAPI = 'https://public.api.bsky.app/xrpc/app.bsky.actor.getProfile?actor={%Handle%}';

	static public string
	$FieldFollowers = 'followersCount';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public string
	$Service = Atlantis\Social\PingDataRow::Bluesky;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Fetch():
	static {

		$URL = Common\Text::TemplateReplaceTokens(static::$ProfileAPI, [
			'Handle' => $this->Handle
		]);

		$Client = Browser\Client::FromURL($URL);
		$JSON = $Client->FetchAsJSON();

		////////

		if(!$JSON)
		throw new Common\Error\RequiredDataMissing('Data from API', 'JSON');

		if(!array_key_exists(static::$FieldFollowers, $JSON))
		throw new Common\Error\RequiredDataMissing(static::$FieldFollowers, 'int');

		////////

		$this->NumFollowers = $JSON[static::$FieldFollowers];

		return $this;
	}

};
