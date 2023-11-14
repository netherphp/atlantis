<?php

namespace Nether\Atlantest; ////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

use Nether\Atlantis;
use Nether\Common;

if(!defined('ProjectRewt'))
define('ProjectRewt', Common\Filesystem\Util::Pathify(
	dirname(__FILE__, 2),
	'app'
));

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class NetworkJsonTestData {

	static public function
	Test1():
	string {
		return trim(<<< EOT
		{
			"website.tld": {
				"Social": {
					"Facebook": "https://facebook.com/websitetld",
					"Instagram": "https://instagram.com/websitetld",
					"Threads": "https://threads.net/websitetld",
					"Twitter": "https://twitter.com/websitetld",
					"YouTube": "https://youtube.com/websitetld",
					"TikTok": "https://tiktok.com/websitetld"
				},
				"Sitemap": {
				}
			},
			"another.tld": {
				"Social": {
					"Facebook": "https://facebook.com/anothertld",
					"Instagram": "https://instagram.com/anothertld",
					"Threads": "https://threads.net/anothertld",
					"Twitter": "https://twitter.com/anothertld",
					"YouTube": "https://youtube.com/anothertld",
					"TikTok": "https://tiktok.com/anothertld"
				},
				"Sitemap": {
				}
			},
			"missingsome.tld": {
				"Social": {
					"Facebook": "https://facebook.com/missingsometld"
				}
			},
			"missingall.tld": { }
		}
		EOT);
	}

};

class NetworkJsonTest
extends Atlantis\Util\Tests\TestCasePU9 {

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestBasic():
	void {

		$Data = Atlantis\Struct\NetworkJSON::FromJSON(
			NetworkJsonTestData::Test1()
		);

		// fetching for something that doesnt exist is null.

		$Site = $Data->Get('doesnotexist.tld');
		$this->AssertNull($Site);

		// fetching a known good config.

		$Site = $Data->Get('website.tld');
		$this->AssertInstanceOf(Atlantis\Struct\NetworkItem::class, $Site);
		$this->AssertInstanceOf(Atlantis\Struct\SocialData::class, $Site->Social);
		$this->AssertEquals(6, $Site->Social->GetCount());
		$this->AssertEquals('https://facebook.com/websitetld', $Site->Social->Get('Facebook'));
		$this->AssertEquals('https://instagram.com/websitetld', $Site->Social->Get('Instagram'));
		$this->AssertEquals('https://threads.net/websitetld', $Site->Social->Get('Threads'));
		$this->AssertEquals('https://tiktok.com/websitetld', $Site->Social->Get('TikTok'));
		$this->AssertEquals('https://twitter.com/websitetld', $Site->Social->Get('Twitter'));
		$this->AssertEquals('https://youtube.com/websitetld', $Site->Social->Get('YouTube'));
		$this->AssertNull($Site->Social->Get('DoesNotExistMyDude'));

		// fetching another known good config.

		$Site = $Data->Get('another.tld');
		$this->AssertEquals(6, $Site->Social->GetCount());
		$this->AssertEquals('https://facebook.com/anothertld', $Site->Social->Get('Facebook'));
		$this->AssertEquals('https://instagram.com/anothertld', $Site->Social->Get('Instagram'));
		$this->AssertEquals('https://threads.net/anothertld', $Site->Social->Get('Threads'));
		$this->AssertEquals('https://tiktok.com/anothertld', $Site->Social->Get('TikTok'));
		$this->AssertEquals('https://twitter.com/anothertld', $Site->Social->Get('Twitter'));
		$this->AssertEquals('https://youtube.com/anothertld', $Site->Social->Get('YouTube'));
		$this->AssertNull($Site->Social->Get('DoesNotExistMyDude'));

		// fetch an incomplete config.

		$Site = $Data->Get('missingsome.tld');
		$this->AssertEquals(1, $Site->Social->GetCount());
		$this->AssertEquals('https://facebook.com/missingsometld', $Site->Social->Get('Facebook'));
		$this->AssertNull($Site->Social->Get('Instagram'));
		$this->AssertNull($Site->Social->Get('Threads'));
		$this->AssertNull($Site->Social->Get('TikTok'));
		$this->AssertNull($Site->Social->Get('Twitter'));
		$this->AssertNull($Site->Social->Get('YouTube'));
		$this->AssertNull($Site->Social->Get('DoesNotExistMyDude'));

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestListing():
	void {

		$Data = Atlantis\Struct\NetworkJSON::FromJSON(
			NetworkJsonTestData::Test1()
		);

		// fetching for something that doesnt exist is null.

		$Site = $Data->Get('website.tld');
		$Items = $Site->Social->GetByList('Facebook', 'Instagram', 'Threads');

		$this->AssertCount(3, $Items);
		$this->AssertArrayHasKey('Facebook', $Items);
		$this->AssertArrayHasKey('Instagram', $Items);
		$this->AssertArrayHasKey('Threads', $Items);
		$this->AssertArrayNotHasKey('TikTok', $Items);
		$this->AssertArrayNotHasKey('Twitter', $Items);
		$this->AssertArrayNotHasKey('YouTube', $Items);
		$this->AssertEquals('https://facebook.com/websitetld', $Items['Facebook']);
		$this->AssertEquals('https://instagram.com/websitetld', $Items['Instagram']);
		$this->AssertEquals('https://threads.net/websitetld', $Items['Threads']);

		return;
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function
	TestIcons():
	void {

		$Data = Atlantis\Struct\NetworkJSON::FromJSON(
			NetworkJsonTestData::Test1()
		);

		$Site = $Data->Get('website.tld');

		$this->AssertEquals('mdi mdi-web', $Site->Social->GetIconStyleClass('Website'));
		$this->AssertEquals('mdi mdi-link-variant', $Site->Social->GetIconStyleClass('DoesNotExist'));

		$this->AssertEquals('si si-facebook', $Site->Social->GetIconStyleClass('Facebook'));
		$this->AssertEquals('si si-instagram', $Site->Social->GetIconStyleClass('Instagram'));
		$this->AssertEquals('si si-threads', $Site->Social->GetIconStyleClass('Threads'));
		$this->AssertEquals('si si-tiktok', $Site->Social->GetIconStyleClass('TikTok'));
		$this->AssertEquals('si si-twitter', $Site->Social->GetIconStyleClass('Twitter'));
		$this->AssertEquals('si si-youtube', $Site->Social->GetIconStyleClass('YouTube'));

		return;
	}

};
