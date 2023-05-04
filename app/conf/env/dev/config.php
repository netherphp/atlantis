<?php

/*//////////////////////////////////////////////////////////////////////////////
// env project settings ////////////////////////////////////////////////////////

this file is for configuration which is specific to the environment it is
currently running on. values set here override values from the application
global config.php file.

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////*/

/**
 * @var Nether\Common\Datastore $Config
 * @var Nether\Atlantis\Engine $App
 */

use Nether\Atlantis;
use Nether\Database;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// PROJECT CONFIG.

($Config)
->Set(Atlantis\Library::ConfProjectDomain, 'localhost')
->Set(Atlantis\Library::ConfContactTo, 'someone@whatever.tld');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// SSL CONFIG

($Config)
->Set(Atlantis\Library::ConfAcmePhar, '/opt/ssl/acmephp.phar')
->Set(Atlantis\Library::ConfAcmeCertRoot, '/opt/ssl')
->Set(Atlantis\Library::ConfAcmeDomain, '')
->Set(Atlantis\Library::ConfAcmeEmail, '')
->Set(Atlantis\Library::ConfAcmeAltDomains, [ ])
->Set(Atlantis\Library::ConfAcmeCountry, '')
->Set(Atlantis\Library::ConfAcmeCity, '')
->Set(Atlantis\Library::ConfAcmeOrgName, '');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// DATABASE CONFIG

($Config)
->Set(Database\Library::ConfConnections, [
	'Default' => new Database\Connection(
		Type:     'mysql',
		Hostname: 'DEV-DB-HOST',
		Database: 'DEV-DB-NAME',
		Username: 'DEV-DB-PASSWORD-HURR',
		Password: 'DEV-DB-PASSWORD-DURR',
		Auto: FALSE
	)
]);
