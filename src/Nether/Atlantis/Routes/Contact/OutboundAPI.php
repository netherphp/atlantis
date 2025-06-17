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

		// error codes
		// 1 - email required
		// 2 - message required
		// 3 - name required
		// 4 - recaptcha invalid
		// 5 - recaptcha api error
		// 6 - phone required
		// 7 - email api error

		($this->Request->Data)
		->FilterPush('Subject', Common\Filters\Numbers::IntType(...))
		->FilterPush('Email', Common\Filters\Text::Email(...))
		->FilterPush('Phone', Common\Filters\Text::TrimmedNullable(...))
		->FilterPush('Message', Common\Filters\Text::TrimmedNullable(...));

		$RequireName  = $this->App->Config->Get(Atlantis\Key::ConfContactRequireName);
		$RequireEmail = $this->App->Config->Get(Atlantis\Key::ConfContactRequireEmail);
		$RequirePhone = $this->App->Config->Get(Atlantis\Key::ConfContactRequirePhone);

		$InputSubject = $this->Request->Data->Get('Subject');
		$InputName    = $this->Request->Data->Get('Name');
		$InputEmail   = $this->Request->Data->Get('Email');
		$InputPhone   = $this->Request->Data->Get('Phone');
		$InputMessage = $this->Request->Data->Get('Message');
		$InputIP      = $this->Request->RemoteAddr;

		$ConfSubject  = $this->ChooseContactSubject($InputSubject);
		$ConfSendTo   = $this->ChooseContactSendTo($InputSubject);
		$ConfReplyTo  = $this->ChooseContactReplyTo($InputEmail, $ConfSendTo);
		$ConfBCC      = $this->ChooseContactBCC();

		$Email = NULL;

		////////

		try {
			if(!Atlantis\Util::IsReCaptchaValid($this->App))
			$this->Quit(4, 'Did not pass ReCaptcha.');
		}

		catch(Exception $Error) {
			$this->Quit(5, $Error->GetMessage());
		}

		////////

		if($RequireName && !$InputName)
		$this->Quit(3, 'Name is required.');

		if($RequireEmail && !$InputEmail)
		$this->Quit(1, 'Email is required.');

		if($RequirePhone && !$InputPhone)
		$this->Quit(6, 'Phone is required.');

		if(!$InputMessage)
		$this->Quit(2, 'Message is required.');

		////////

		$Email = new Email\Outbound;

		$Email->Subject = $ConfSubject;
		$Email->ReplyTo = $ConfReplyTo;
		$Email->To->MergeRight($ConfSendTo);
		$Email->BCC->MergeRight($ConfBCC);

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
		catch(Exception $E) { /* $this->Quit(7); */ }

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
			'Content' => $Email->Content
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

	public function
	ChooseContactBCC():
	array {

		$Who = $this->App->Config->Get(Atlantis\Key::ConfContactBCC);

		// handle invalid configs.

		if(!$Who)
		return [];

		if(!is_string($Who) && !is_array($Who))
		return [];

		// handle valid configs.

		if(is_string($Who))
		return [ $Who ];

		return $Who;
	}

	public function
	ChooseContactReplyTo(?string $Email, array $SendTo):
	string {

		return match(TRUE) {
			(is_string($Email) && strlen($Email))
			=> $Email,

			default
			=> current($SendTo)
		};;
	}

}
