<?php

namespace Nether\Atlantis\Routes\Admin;

use Nether\Atlantis;
use Nether\Common;
use Nether\Email;
use Nether\User;

class EmailWeb
extends Atlantis\ProtectedWeb {

	#[Atlantis\Meta\RouteHandler('/ops/email')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	#[Atlantis\Meta\TrafficReportSkip]
	public function
	HandleTestGet():
	void {

		$Config = new Email\Struct\LibraryConfigInfo;

		($this)
		->SetPageTitle('Email Config // Operations')
		->Area('admin/email/index', [
			'Config' => $Config
		]);

		return;
	}

	#[Atlantis\Meta\RouteHandler('/ops/email', Verb: 'POST')]
	#[Atlantis\Meta\RouteAccessTypeAdmin]
	public function
	HandleTestPost():
	void {

		($this->Data)
		->Email(Common\Filters\Text::Email(...))
		->Via(Common\Filters\Text::StringNullable(...));

		if($this->Data->Email && $this->Data->Via) {
			$this->SendTestEmail($this->Data->Email, $this->Data->Via);
			$this->Surface->Set('Admin.Email.TestSent', TRUE);
		}

		$this->Surface->Set('TestEmailSent', 'TRUE');
		$this->HandleTestGet();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SendTestEmail(string $SendTo, string $Via):
	void {

		$Email = new Email\Outbound;

		$Email->To->Push($SendTo);
		$Email->Subject = 'Test Email';

		$Email->Content = '<h2>This is a test email.</h2>';
		$Email->Content .= '<p>It was sent as a test.</p>';
		$Email->Content .= sprintf(
			'<p>Sent from the admin panel via the configured %s.</p>',
			$Email::GetViaName($Via)
		);

		$Email->Send($Via);

		return;
	}

}
