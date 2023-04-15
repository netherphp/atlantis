<?php

namespace Nether\Atlantis\Routes\Contact;

use Nether\Atlantis;
use Nether\Common;
use Nether\Email;

use Exception;

class OutboundAPI
extends Atlantis\PublicAPI {

	#[Atlantis\Meta\RouteHandler('/api/contact/send', Verb: 'POST')]
	public function
	HandleSend():
	void {
	/*//
	provide an endpoint for the 'contact us' form on the website. it takes
	an end user supplied message and sends it to the configured primary
	contact.
	//*/

		($this->Request->Data)
		->Email(Common\Datafilters::Email(...))
		->Message(Common\Datafilters::TrimmedTextNullable(...));

		////////

		try {
			if(!Atlantis\Util::IsReCaptchaValid($this->App))
			$this->Quit(4, 'Did not pass ReCaptcha.');
		}

		catch(Exception $Error) {
			$this->Quit(5, $Error->GetMessage());
		}

		////////

		$SendTo = Atlantis\Library::Get(Atlantis\Library::ConfContactTo);
		$SendBCC = Atlantis\Library::Get(Atlantis\Library::ConfContactBCC);
		$SendSubject = Atlantis\Library::Get(Atlantis\Library::ConfContactSubject);

		if($SendTo && !is_array($SendTo))
		$SendTo = [ $SendTo ];

		if($SendBCC && !is_array($SendBCC))
		$SendBCC = [ $SendBCC ];

		////////

		$InputName = $this->Request->Data->Name;
		$InputEmail = $this->Request->Data->Email;
		$InputMessage = $this->Request->Data->Message;
		$InputIP = $this->Request->RemoteAddr;

		if(!$InputName)
		$this->Quit(3, 'Name is required');

		if(!$InputEmail)
		$this->Quit(1, 'Email is required');

		if(!$InputMessage)
		$this->Quit(2, 'Message is required');

		////////

		$Email = new Email\Outbound;
		$Email->Subject = $SendSubject;
		$Email->ReplyTo = $InputEmail;

		if($SendTo && count($SendTo))
		$Email->To->MergeLeft($SendTo);

		if($SendBCC && count($SendBCC))
		$Email->BCC->MergeLeft($SendBCC);

		$Email->Render('email/contact-form', [
			'IP'      => $InputIP,
			'Email'   => $InputEmail,
			'Name'    => $InputName,
			'Subject' => $SendSubject,
			'Message' => $InputMessage
		]);

		////////

		try { $Email->Send(); }
		catch(Exception $E) {

		}

		Atlantis\Struct\ContactEntry::Insert([
			'IP'      => $InputIP,
			'Name'    => $InputName,
			'Email'   => $InputEmail,
			'Subject' => $SendSubject,
			'Message' => $InputMessage
		]);

		////////

		$this->SetPayload([
			'Subject' => $Email->Subject,
			'From'    => $Email->From,
			'ReplyTo' => $Email->ReplyTo,
			'To'      => join(', ', $Email->To->GetData()) ?: NULL,
			'BCC'     => join(', ', $Email->BCC->GetData()) ?: NULL,
			'Content' => $Email->Content
		]);

		return;
	}

}
