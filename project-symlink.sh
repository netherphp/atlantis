#!/bin/bash

PROOT=`pwd`
SROOT=`dirname $(readlink -f "$0")`

################################################################################
################################################################################

echo "ProjectRoot: $PROOT"
echo "SourceRoot: $SROOT"

echo ""
echo "This will delete some files from your ProjectRoot and symlink them with their counterparts in the SourceRoot."
echo ""

read -p "Are you sure? [y/n] " -n 2 -r

if [[ ! $REPLY =~ ^[Yy]$ ]]
then
	echo "Laters."
	exit 1
fi

################################################################################
################################################################################

echo "Symlinking in www/index.php..."
rm $PROOT/www/index.php
ln -s $SROOT/app/www/index.php $PROOT/www/index.php

echo "Symlinking in www/themes/default..."
rm -rf $PROOT/www/themes/default
ln -s $SROOT/app/www/themes/default $PROOT/www/themes/default

echo "Symlinking in www/share/atlantis..."
rm -rf $PROOT/www/share/atlantis
ln -s $SROOT/app/www/share/atlantis $PROOT/www/share/atlantis

################################################################################
################################################################################

echo "Symlinking in routes/Home.php..."
rm $PROOT/routes/Home.php
ln -s $SROOT/app/routes/Home.php $PROOT/routes/Home.php

echo "Symlinking in routes/Docs.php..."
rm $PROOT/routes/Docs.php
ln -s $SROOT/app/routes/Docs.php $PROOT/routes/Docs.php
