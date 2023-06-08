<?php

namespace Nether\Atlantis\Util;

use Nether\Atlantis;
use Nether\Common;

use Throwable;

class CertInfo
extends Common\Prototype {

	const
	StatusExpired         = 0,
	StatusOK              = 1,
	StatusExpireSoon      = 2,
	StatusExpireWarning   = 3;

	const
	StatusWords = [
		self::StatusExpired        => 'EXPIRED',
		self::StatusOK             => 'OK',
		self::StatusExpireSoon     => 'SOON',
		self::StatusExpireWarning  => 'IMMINENT'
	];

	////////

	public string
	$Domain;

	public int
	$TimeStart;

	public int
	$TimeExpire;

	public string
	$Source;

	////////

	#[Common\Meta\PropertyFactory('FromTime', 'TimeStart')]
	public Common\Date
	$DateStart;

	#[Common\Meta\PropertyFactory('FromTime', 'TimeExpire')]
	public Common\Date
	$DateExpire;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetStatusCode():
	int {

		$Diff = $this->GetTimeframe();
		$Dist = $Diff->GetTimeDiff();

		////////

		if($Dist <= 0)
		return static::StatusExpired;

		if($Dist <= (Common\Values::SecPerDay * 2))
		return static::StatusExpireWarning;

		if($Dist <= (Common\Values::SecPerDay * 7))
		return static::StatusExpireSoon;

		////////

		return static::StatusOK;
	}

	public function
	GetStatusWord():
	string {

		$Status = $this->GetStatusCode();

		if(isset(static::StatusWords[$Status]))
		return static::StatusWords[$Status];

		return 'UNKNOWN';
	}

	public function
	GetTimeframe():
	Common\Units\Timeframe {

		return new Common\Units\Timeframe(
			(new Common\Date)->GetUnixtime(),
			$this->DateExpire->GetUnixtime(),
			Common\Units\Timeframe::FormatShorter,
			Precision: 2
		);
	}

	public function
	IsExpired():
	bool {

		$Diff = $this->GetTimeframe();
		$Dist = $Diff->GetTimeDiff();

		if($Dist <= 0)
		return TRUE;

		return FALSE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FetchViaOpenSSL(string $Domain):
	static {

		$Output = NULL;
		$StErr = NULL;
		$StMsg = NULL;

		////////

		$Context = stream_context_create([
			'ssl' => [
				'capture_peer_cert' => TRUE,
				'verify_peer'       => FALSE
			]
		]);

		$Client = @stream_socket_client(
			sprintf('ssl://%s:443', $Domain),
			$StErr, $StMsg,
			30, STREAM_CLIENT_CONNECT,
			$Context
		);

		if(!$Client)
		throw new Atlantis\Error\CertLookupFailure("OPENSSL_CLIENT({$Domain})");

		$Cert = stream_context_get_params($Client);
		$Info = openssl_x509_parse($Cert['options']['ssl']['peer_certificate']);

		////////

		if(!is_array($Info))
		throw new Atlantis\Error\CertLookupFailure("OPENSSL_STREAM({$Domain})");

		if(!isset($Info['subject']) || !isset($Info['subject']['CN']))
		throw new Atlantis\Error\CertLookupUnexpectedFormat($Domain, 'no [subject][CN] in openssl result');

		if(!isset($Info['validFrom_time_t']))
		throw new Atlantis\Error\CertLookupUnexpectedFormat($Domain, 'no [validFrom_time_t] in openssl result');

		if(!isset($Info['validTo_time_t']))
		throw new Atlantis\Error\CertLookupUnexpectedFormat($Domain, 'no [validTo_time_t] in openssl result');

		/////////

		$Output = new static([
			'Domain'     => $Info['subject']['CN'],
			'TimeStart'  => $Info['validFrom_time_t'],
			'TimeExpire' => $Info['validTo_time_t'],
			'Source'     => 'OpenSSL'
		]);

		return $Output;
	}

	static public function
	FetchViaCurl(string $Domain):
	static {

		$Curl = curl_init();
		$Result = NULL;
		$Info = NULL;

		////////

		curl_setopt($Curl, CURLOPT_URL, sprintf('https://%s', $Domain));
		curl_setopt($Curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($Curl, CURLOPT_CERTINFO, TRUE);
		curl_setopt($Curl, CURLOPT_RETURNTRANSFER, TRUE);

		if(!curl_exec($Curl))
		throw new Atlantis\Error\CertLookupFailure("CURL({$Domain})");

		$Info = curl_getinfo($Curl, CURLINFO_CERTINFO);
		curl_close($Curl);

		////////

		if(!is_array($Info) || !isset($Info[0]))
		throw new Atlantis\Error\CertLookupFailure("CURLRESULT({$Domain})");

		if(!isset($Info[0]['Start date']))
		throw new Atlantis\Error\CertLookupUnexpectedFormat($Domain, 'no [Start date] in curl result');

		if(!isset($Info[0]['Expire date']))
		throw new Atlantis\Error\CertLookupUnexpectedFormat($Domain, 'no [Expire date] in curl result');

		////////

		$Output = new static([
			'Domain'     => $Domain,
			'TimeStart'  => (new Common\Date($Info[0]['Start date']))->GetUnixtime(),
			'TimeExpire' => (new Common\Date($Info[0]['Expire date']))->GetUnixtime(),
			'Source'     => 'cURL'
		]);

		return $Output;
	}

}
