#!/bin/bash

export RepoURL="https://github.com/acmesh-official/acme.sh"
export TempDir="/tmp/acmesh"

export InstallDir="/opt/acmesh"
export ConfigDir="/opt/acmesh/local/confs"
export CertDir="/opt/acmesh/local/certs"

# this helper script is to get acme.sh installed on in a method that feels
# generally decent for allowing all the sites on the server to use it. it
# should even set up a cron in the user that ran it to automate the renewals.

# after acme.sh does its job then my apache configurations tend to look like
# this here.

# SSLCertificateFile    "/opt/acmesh/local/certs/$SSLDomain_ecc/$SSLDomain.cer"
# SSLCertificateKeyFile "/opt/acmesh/local/certs/$SSLDomain_ecc/$SSLDomain.key"
# SSLCACertificateFile  "/opt/acmesh/local/certs/$SSLDomain_ecc/ca.cer"

################################################################################
################################################################################

OriginDir=`pwd`
ContactEmail=$1

ShowUsageInfo() {
	echo "USAGE: $0 <ssl-contact-email>"
	echo ""
}

################################################################################
################################################################################

if [ -z "$ContactEmail" ];
then
	ShowUsageInfo
	exit 1
fi

if [ -d $InstallDir ];
then
	echo "there appears an install already at $InstallDir"
	exit 2
fi

################################################################################
################################################################################

OptInstall="--home $InstallDir --cert-home $CertDir --config-home $ConfigDir"

# grab the code.

rm -rf $TempDir
git clone $RepoURL $TempDir

# run its installer with the config.

cd $TempDir
bash ./acme.sh --install $OptInstall -m $ContactEmail

# cleanup the install.

cd $OriginDir
rm -rf $TempDir

exit 0
