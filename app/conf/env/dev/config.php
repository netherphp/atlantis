<?php

/**
 * @var Nether\Object\Datastore $Config
 * @var Nether\Atlantis\Engine $App
 */

$Config
->Set('AcmePHP.Phar', '/opt/ssl/acmephp.phar')
->Set('AcmePHP.CertRoot', '/opt/ssl')
->Set('AcmePHP.Domain', '')
->Set('AcmePHP.Email', '')
->Set('AcmePHP.AltDomains', [])
->Set('AcmePHP.Country', '')
->Set('AcmePHP.City', '')
->Set('AcmePHP.OrgName', '');
