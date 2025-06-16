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
		->Subject(Common\Filters\Numbers::IntType(...))
		->Email(Common\Filters\Text::Email(...))
		->Phone(Common\Filters\Text::TrimmedNullable(...))
		->Message(Common\Filters\Text::TrimmedNullable(...));

		////////

		try {
			if(!Atlantis\Util::IsReCaptchaValid($this->App))
			$this->Quit(4, 'Did not pass ReCaptcha.');
		}

		catch(Exception $Error) {
			$this->Quit(5, $Error->GetMessage());
		}

		////////

		//$SendTo = Atlantis\Library::Get(Atlantis\Key::ConfContactTo);
		$SendBCC = Atlantis\Library::Get(Atlantis\Key::ConfContactBCC);
		//$SendSubject = Atlantis\Library::Get(Atlantis\Key::ConfContactSubject);

		//if($SendTo && !is_array($SendTo))
		//$SendTo = [ $SendTo ];

		if($SendBCC && !is_array($SendBCC))
		$SendBCC = [ $SendBCC ];

		////////

		$InputSubject = $this->Request->Data->Subject;
		$InputName = $this->Request->Data->Name;
		$InputEmail = $this->Request->Data->Email;
		$InputPhone = $this->Request->Data->Phone;
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
		//$Email->Subject = $SendSubject;
		$Email->ReplyTo = $InputEmail;

		//if($SendTo && count($SendTo))
		//$Email->To->MergeLeft($SendTo);

		if($SendBCC && count($SendBCC))
		$Email->BCC->MergeLeft($SendBCC);

		////////

		$ConfSubject = $this->ChooseContactSubject($InputSubject);
		$ConfSendTo = $this->ChooseContactSendTo($InputSubject);

		$Email->Subject = $ConfSubject;
		$Email->To->MergeRight($ConfSendTo);

		////////

		$Email->Render('email/contact-form', [
			'IP'      => $InputIP,
			'Name'    => $InputName,
			'Email'   => $InputEmail,
			'Phone'   => $InputPhone,
			'Subject' => $ConfSubject,
			'Message' => $InputMessage
		]);

		////////

		try { $Email->Send(); }
		catch(Exception $E) {

		}

		if(Atlantis\Struct\ContactEntry::HasDB())
		Atlantis\Struct\ContactEntry::Insert([
			'IP'      => $InputIP,
			'Name'    => $InputName,
			'Email'   => $InputEmail,
			'Phone'   => $InputPhone,
			'Subject' => $Email->Subject,
			'SentTo'  => $Email->To->Join(', '),
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

	public function
	FetchContactSubjectEndpoints():
	Common\Datastore {

		$Configured = $this->App->Config->Get(Email\Library::ConfSubjectEndpoints);

		if(!is_countable($Configured) || !count($Configured))
		$Configured = [
			Atlantis\Key::ConfContactSubject
			=> [ $this->App->Config->Get(Atlantis\Key::ConfContactTo) ]
		];

		////////

		$Output = new Common\Datastore($Configured);

		return $Output;
	}

	public function
	ChooseContactSubject(int $Subject):
	string {

		$Valids = $this->FetchContactSubjectEndpoints()->Keys();

		return match(TRUE) {
			array_key_exists($Subject, $Valids)
			=> $Valids[$Subject],

			default
			=> $Valids[0]
		};
	}

	public function
	ChooseContactSendTo(int $Subject):
	array {

		$Valids = $this->FetchContactSubjectEndpoints()->Values(TRUE);

		$Choose = match(TRUE) {
			array_key_exists($Subject, $Valids)
			=> $Valids[$Subject],

			default
			=> $Valids[0]
		};

		if(!is_array($Choose))
		$Choose = [ $Choose ];

		return $Choose;
	}

}
