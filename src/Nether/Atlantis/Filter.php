<?php

namespace Nether\Atlantis;

class Filter {

	static public function
	EncodeHTML(string|NULL $Input):
	string {

		return htmlentities($Input ?? '');
	}

}
