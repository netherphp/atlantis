<?php

/**
 * @var Nether\Object\Datastore $Config
 * @var Nether\Atlantis\Engine $App
 */

($Config)
->Set('Project.Name', 'Atlantis WebApp')
->Set('Project.DescShort', 'Example project built on Nether Atlantis')
->Set('Project.Key', 'atlantis')
->Set('Project.WebServerType', NULL)
->Set(Nether\Avenue\Library::ConfRouteFile, $App->FromProjectRoot('routes.phson'))
->Set(Nether\Avenue\Library::ConfRouteRoot, $App->FromProjectRoot('routes'));
