<?php

namespace Nether\Atlantis\Packages\DevTool;

use Nether\Atlantis;
use Nether\Common;
use Nether\Console;

trait QueryDomainLineList {

	#[Common\Meta\Date('2023-11-10')]
	#[Common\Meta\Info('Engage an interaction mode to extract domain configuration from the user.')]
	public function
	QueryDomainLineList(Common\Datastore $Domains):
	Common\Datastore {

		// [
		// 	"primary.tld secondary.tld tertiary.tld"
		// 	...
		// ]

		// the first domain is used as the domain that owns the ssl
		// and the secondary domains all grift off that cert in the
		// webserver config. in apache speak it would look like this:

		// Use HTTPS primary.tld primary.tld /web/root
		// Use HTTPS secondary.tld primary.tld /web/root
		// Use HTTPS tertiary.tld primary.tld /web/root

		$Index = (
			($Domains)
			->MapKeys(fn(string $K, string $D)=> [ explode(' ', $D, 2)[0] => $D ])
			->Filter(fn(string $D)=> !!$D)
		);

		(function(Common\Datastore $Index) {
			$Input = NULL;
			$Line = NULL;

			while(TRUE) {
				$this->PrintLn($this->FormatHeaderPoint(
					'Current Config:',
					Console\Theme::Accent
				), 2);

				$this->PrintLn(match(TRUE) {
					$Index->Count() > 0
					=> rtrim($this->FormatBulletList($Index)),

					default
					=> $this->Format('No domains have been added yet.', Console\Theme::Muted)
				}, 2);

				$this->PrintLn($this->FormatTopicList([
					'Set or overwrite Domain Line'
					=> sprintf('> %s primary.tld %s', $this->Format('set', Console\Theme::Accent), $this->Format('secondary.tld tertiary.tld', Console\Theme::Muted)),

					'Add Domain to existing Domain Line.'
					=> sprintf('> %s primary.tld to-append.tld', $this->Format('add', Console\Theme::Accent)),

					'Delete Domain or Domain Line.'
					=> sprintf('> %s primary.tld %s', $this->Format('del', Console\Theme::Accent), $this->Format('to-delete.tld', Console\Theme::Muted))
				], Console\Theme::Strong, Console\Theme::Default), 2);

				////////

				$Input = $this->PromptForValue(
					'Domain Command',
					'[set|add|del]',
					FALSE
				);

				if($Input === NULL)
				break;

				////////

				$Line = new Atlantis\Struct\DomainLine($Input, TRUE);

				if(!$Line->IsConfigured()) {
					$this->PrintLn($this->FormatHeaderPoint(
						'ERROR: Failed to parse input.',
						Console\Theme::Error
					), 2);

					continue;
				}

				////////

				if(str_starts_with($Input, 'set')) {
					$Index[$Line->Primary] = $Line->ToString();
					continue;
				}

				if(str_starts_with($Input, 'add')) {
					if(!$Index->HasKey($Line->Primary))
					$Index[$Line->Primary] = $Line->Primary;

					if($Line->Secondary)
					$Index[$Line->Primary] = Atlantis\Struct\DomainLine::MergeLines(
						$Index[$Line->Primary],
						$Line->Secondary
					);

					continue;
				}

				if(str_starts_with($Input, 'del')) {
					if(!$Index->HasKey($Line->Primary)) {
						$this->PrintLn($this->FormatHeaderPoint(
							"ERROR: No Domain Line {$Line->Primary} found",
							Console\Theme::Error
						), 2);

						continue;
					}

					if($Line->Secondary) {
						$Index[$Line->Primary] = Atlantis\Struct\DomainLine::Strip(
							$Index[$Line->Primary],
							$Line->Secondary
						);

						continue;
					}

					unset($Index[$Line->Primary]);
					continue;
				}

				////////

				continue;
			}

			return;
		})($Index);

		return $Index->Values();
	}

};
