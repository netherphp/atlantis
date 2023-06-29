<?php

/*//////////////////////////////////////////////////////////////////////////////
// global project settings /////////////////////////////////////////////////////

this file is for main project configuration which should be consistent across
any of the environments it should run on.

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////*/

/**
 * @var Nether\Common\Datastore $Config
 * @var Nether\Atlantis\Engine $App
 */

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\Email;
use Nether\Storage;
use Nether\User;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// PROJECT CONFIG.

($Config)
->Set(Atlantis\Library::ConfProjectID, 'atlantis')
->Set(Atlantis\Library::ConfProjectName, 'Atlantis WebApp')
->Set(Atlantis\Library::ConfProjectDomain, 'whatever.tld')
->Set(Atlantis\Library::ConfProjectDescShort, 'Example project built on Nether Atlantis')
->Set(Atlantis\Library::ConfProjectWebserver, Atlantis\Library::WebServerTypeApache24)
->Set(Atlantis\Library::ConfProjectWebCertType, Atlantis\Library::WebCertTypeAcmePHP)
->Set(Atlantis\Library::ConfContactTo, 'someone@whatever.tld');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// FRAMEWORK CONFIG.

($Config)
->Set(Avenue\Library::ConfRouteFile, $App->FromProjectRoot('routes.phson'))
->Set(Avenue\Library::ConfRouteRoot, $App->FromProjectRoot('routes'))
->Set(Atlantis\Library::ConfUserAllowLogin, FALSE)
->Set(Atlantis\Library::ConfUserAllowSignup, FALSE)
->Set(Atlantis\Library::ConfLibraries, [ ])
->Set(Storage\Library::ConfStorageLocations, [
	new Storage\Adaptors\Local(
		Name: 'Default',
		Root: $App->FromProjectRoot('data'),
		URL: '/data/{Path}'
	),
	new Storage\Adaptors\Local(
		Name: 'Temp',
		Root: $App->FromProjectRoot('temp')
	)
]);

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// USER CONFIG.

($Config)
->Set(User\Library::ConfAppleEnabled, FALSE)
->Set(User\Library::ConfAppleID, '')
->Set(User\Library::ConfAppleTeamID, '')
->Set(User\Library::ConfAppleKeyFileID, '')
->Set(User\Library::ConfGitHubEnabled, FALSE)
->Set(User\Library::ConfGitHubID, '')
->Set(User\Library::ConfGitHubSecret, '')
->Set(User\Library::ConfGoogleEnabled, FALSE)
->Set(User\Library::ConfGoogleID, '')
->Set(User\Library::ConfGoogleSecret, '')
->Set(User\Library::ConfDiscordEnabled, FALSE)
->Set(User\Library::ConfDiscordID, '')
->Set(User\Library::ConfDiscordSecret, '');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// EMAIL CONFIG.

($Config)
->Set(Email\Library::ConfLogFile, $App->FromProjectRoot('logs/{Y}/{M}/email.log'))
->Set(Email\Library::ConfSendGridKey, '')
->Set(Email\Library::ConfMailjetPublicKey, '')
->Set(Email\Library::ConfMailjetPrivateKey, '')
->Set(Email\Library::ConfServerHost, '')
->Set(Email\Library::ConfServerPort, 587)
->Set(Email\Library::ConfServerUsername, '')
->Set(Email\Library::ConfServerPassword, '')
->Set(Email\Library::ConfOutboundVia, NULL)
->Set(Email\Library::ConfOutboundFrom, 'info@whatever.tld')
->Set(Email\Library::ConfOutboundReplyTo, 'who@whatever.tld')
->Set(Email\Library::ConfOutboundName, 'Info');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// MISC CONFIG.

($Config)
->Set(Common\Date::ConfDefaultTimezone, 'America/Chicago');
