<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\Email;
use Nether\User;

class EmailWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/email/test')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleTest():
	void {

		$DefaultService = Email\Library::Get(Email\Library::ConfOutboundVia);
		$DefaultName = Email\Outbound::GetViaName($DefaultService);

		$ServiceConfigs = [
			Email\Outbound::GetViaName(Email\Outbound::ViaSMTP)
			=> (object)[
				'Ready'     => Email\Library::IsConfiguredSMTP(),
				'Value'     => Email\Outbound::ViaSMTP,
				'IsDefault' => ($DefaultService === Email\Outbound::ViaSMTP),
				'Keys'      => [
					Email\Library::ConfServerHost
					=> 'Email\Library::ConfServerHost',
					Email\Library::ConfServerPort
					=> 'Email\Library::ConfServerPort',
					Email\Library::ConfServerUsername
					=> 'Email\Library::ConfServerUsername',
					Email\Library::ConfServerPassword
					=> 'Email\Library::ConfServerPassword'
				]
			],
			Email\Outbound::GetViaName(Email\Outbound::ViaSendGrid)
			=> (object)[
				'Ready'     => Email\Library::IsConfiguredSendGrid(),
				'Value'     => Email\Outbound::ViaSendGrid,
				'IsDefault' => ($DefaultService === Email\Outbound::ViaSendGrid),
				'Keys'     => [
					Email\Library::ConfSendGridKey
					=> 'Email\Library::ConfSendGridKey'
				]
			],
			Email\Outbound::GetViaName(Email\Outbound::ViaMailjet)
			=> (object)[
				'Ready'     => Email\Library::IsConfiguredMailjet(),
				'Value'     => Email\Outbound::ViaMailjet,
				'IsDefault' => ($DefaultService === Email\Outbound::ViaMailjet),
				'Keys'     => [
					Email\Library::ConfMailjetPublicKey
					=> 'Email\Library::ConfMailjetPublicKey',
					Email\Library::ConfMailjetPrivateKey
					=> 'Email\Library::ConfMailjetPrivateKey'
				]
			]
		];

		$this->Surface
		->Set('Page.Title', 'Email Sending Test')
		->Wrap('admin/email/test', [
			'DefaultService' => $DefaultService,
			'DefaultName'    => $DefaultName,
			'ServiceConfigs' => $ServiceConfigs
		]);

		return;
	}

}
