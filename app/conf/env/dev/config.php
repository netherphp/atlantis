<?php

/*//////////////////////////////////////////////////////////////////////////////
// env project settings ////////////////////////////////////////////////////////

this file is for configuration which is specific to the environment it is
currently running on.

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////*/

use Nether\Atlantis;
use Nether\Common;
use Nether\Database;
use Nether\User;

/**
 * @var Nether\Object\Datastore $Config
 * @var Nether\Atlantis\Engine $App
 */

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

/*
($Config)
->Set(Database\Library::ConfConnections, [
	'Default' => new Database\Connection(
		Type:     'mysql',
		Hostname: 'DB-HOST',
		Database: 'DB-NAME',
		Username: 'DB-PASSWORD-HURR',
		Password: 'DB-PASSWORD-DURR'
	)
]);
*/
