<?php

namespace Nether\Atlantis;

class Key {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	ConfProjectID             = 'Project.Key',
	ConfProjectName           = 'Project.Name',
	ConfProjectDomain         = 'Project.Domain',
	ConfProjectDesc           = 'Project.Desc',
	ConfProjectDescShort      = 'Project.DescShort',
	ConfProjectKeywords       = 'Project.Keywords',
	ConfProjectDefineConsts   = 'Project.DefineConstants',
	ConfProjectInitWithConfig = 'Project.InitWithConfig',
	ConfProjectLogoURL        = 'Project.LogoURL',
	ConfProjectWebRoot        = 'Project.WebRoot',
	ConfProjectWebserver      = 'Project.WebServerType',
	ConfProjectWebCertType    = 'Project.WebCertType';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const // @deprecated 2023-10-13 moved to atlantis.json
	ConfAcmePhar       = 'AcmePHP.Phar',
	ConfAcmeCertRoot   = 'AcmePHP.CertRoot',
	ConfAcmeDomain     = 'AcmePHP.Domain',
	ConfAcmeEmail      = 'AcmePHP.Email',
	ConfAcmeAltDomains = 'AcmePHP.AltDomains',
	ConfAcmeCountry    = 'AcmePHP.Country',
	ConfAcmeCity       = 'AcmePHP.City',
	ConfAcmeOrgName    = 'AcmePHP.OrgName';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	ConfDevProdSendOff        = 'Nether.Atlantis.DevProdSendOff';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	ConfLibraries             = 'Nether.Atlantis.Libraries',
	ConfLogFormat             = 'Nether.Atlantis.Log.Format',
	ConfPassMinLen            = 'Nether.Atlantis.Passwords.MinLen',
	ConfPassReqAlphaLower     = 'Nether.Atlantis.Passwords.RequireAlphaLower',
	ConfPassReqAlphaUpper     = 'Nether.Atlantis.Passwords.RequireAlphaUpper',
	ConfPassReqNumeric        = 'Nether.Atlantis.Passwords.RequireNumeric',
	ConfPassReqSpecial        = 'Nether.Atlantis.Passwords.RequireSpecial',
	ConfUserAllowLogin        = 'Nether.Atlantis.Users.AllowLogin',
	ConfUserAllowSignup       = 'Nether.Atlantis.Users.AllowSignup',
	ConfUserAllowSignupGank   = 'Nether.Atlantis.Users.AllowSignupGank',
	ConfUserEmailActivate     = 'Nether.Atlantis.Users.EmailActivation',
	ConfUserRequireAlias      = 'Nether.Atlantis.Users.RequireAlias',
	ConfContactTo             = 'Nether.Atlantis.Contact.To',
	ConfContactBCC            = 'Nether.Atlantis.Contact.BCC',
	ConfContactSubject        = 'Nether.Atlantis.Contact.Subject',
	ConfErrorDisplay          = 'Nether.Atlantis.Error.Display',
	ConfErrorLogPath          = 'Nether.Atlantis.Error.LogPath',
	ConfAccessIgnoreAgentHard = 'Nether.Atlantis.Access.IgnoreAgentHard',
	ConfAccessIgnoreAgentSoft = 'Nether.Atlantis.Access.IgnoreAgentSoft',
	ConfUserAgent             = 'Nether.Atlantis.UserAgent',
	ConfTrafficReporting      = 'Nether.Atlantis.TrafficReporting',
	ConfDevLinkRewriter       = 'Nether.Atlantis.DevLinkRewrite',
	ConfNetworkJSON           = 'Nether.Atlantis.Network.Filename';


	const
	ConfPageEnableDB          = 'Nether.Atlantis.Page.EnableDatabase',
	ConfPageEnableStatic      = 'Nether.Atlantis.Page.EnableStatic',
	ConfPageStaticStorageKey  = 'Nether.Atlantis.Page.StaticStorageKey',
	ConfPageStaticStoragePath = 'Nether.Atlantis.Page.StaticStoragePath';

	const
	WebServerTypeNone     = NULL,
	WebServerTypeApache24 = 'apache24';

	const
	WebCertTypeAcmePHP = 'acmephp';

	const
	AccessContentLog       = 'Nether.Atlantis.Access.ContentLog',
	AccessTrafficLog       = 'Nether.Atlantis.Access.TrafficLog',
	AccessContactLogManage = 'Nether.Atlantis.ContactLog.Manage',
	AccessPageManage       = 'Nether.Atlantis.Page.Manage',
	AccessDeveloper        = 'Nether.Atlantis.Developer',
	AccessTagAdmin         = 'Nether.Atlantis.Tags.Admin';

	const
	PageTagIndexURL = 'Nether.Atlantis.Tag.PageIndexURL',
	PageTagViewURL  = 'Nether.Atlantis.Tag.PageViewURL';

	const
	ConfSiteTags = 'Nether.Atlantis.SiteTags';

};
