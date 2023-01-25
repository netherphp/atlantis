<?php

/*//////////////////////////////////////////////////////////////////////////////
// global project settings /////////////////////////////////////////////////////

this file is for main project configuration which should be consistenta cross
any of the environments it should run on.

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////*/

use Nether\Atlantis;
use Nether\Avenue;
use Nether\Common;
use Nether\User;

/**
 * @var Nether\Common\Datastore $Config
 * @var Nether\Atlantis\Engine $App
 */

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// PROJECT CONFIG.

($Config)
->Set(Atlantis\Library::ConfProjectID, 'atlantis')
->Set(Atlantis\Library::ConfProjectName, 'Atlantis WebApp')
->Set(Atlantis\Library::ConfProjectDescShort, 'Example project built on Nether Atlantis')
->Set(Atlantis\Library::ConfProjectWebserver, NULL);

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// ATLANTIS CONFIG.

($Config)
->Set(Atlantis\Library::ConfUserAllowLogin, FALSE)
->Set(Atlantis\Library::ConfUserAllowSignup, FALSE)
->Set(Atlantis\Library::ConfLibraries, [ ])
->Set(Avenue\Library::ConfRouteFile, $App->FromProjectRoot('routes.phson'))
->Set(Avenue\Library::ConfRouteRoot, $App->FromProjectRoot('routes'));

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

// MISC CONFIG.

($Config)
->Set(Common\Date::ConfDefaultTimezone, 'America/Chicago');
