<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Social\Services;

use Nether\Atlantis;
use Nether\Browser;
use Nether\Common;

################################################################################
################################################################################

class Mastodon
extends Atlantis\Social\Service {

	static public string
	$Key = 'mastodon';

	static public string
	$Name = 'Mastodon';

	static public string
	$Icon = 'si si-mastodon';

	////////

	static public string
	$ProfileAPI = '/api/v1/accounts/lookup?acct={%Handle%}';

	static public string
	$FieldFollowers = 'followers_count';

	////////////////////////////////////////////////////////////////
	// overrides ///////////////////////////////////////////////////

	public function
	Fetch():
	static {

		$User = NULL;
		$Host = NULL;
		$ProfileAPI = NULL;
		$URL = NULL;
		$Client = NULL;
		$JSON = NULL;

		////////

		// mastodon being distributed the username contains their
		// main host instance that we need to ping.

		list($User, $Host) = explode('@', $this->Handle);

		$ProfileAPI = sprintf(
			'https://%s%s',
			$Host, static::$ProfileAPI
		);

		$URL = Common\Text::TemplateReplaceTokens($ProfileAPI, [
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

	public function
	GetURL():
	string {

		list($User, $Host) = explode('@', $this->Handle);

		$User = ltrim($User, '@');

		return sprintf('https://%s/@%s', $Host, $User);
	}

	public function
	SetHandle(string $Handle):
	static {

		// normalise by peeling off the leading at sign.

		$Handle = ltrim(ltrim($Handle), '@');

		// confirm that it looks like an email address now.

		$Handle = Common\Filters\Text::Email($Handle);

		if(!$Handle)
		throw new Common\Error\FormatInvalid(
			'Mastodon handles look like Email addresses: user@instance.tld'
		);

		////////

		return parent::SetHandle($Handle);
	}


};
