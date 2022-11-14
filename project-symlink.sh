#!/bin/sh

ROOT=`pwd`

# demo router and applications.

rm ../../../routes/Docs.php
ln -s $ROOT/app/routes/Docs.php ../../../routes/Docs.php

rm ../../../routes/Home.php
ln -s $ROOT/app/routes/Home.php ../../../routes/Home.php

# default themes.

rm -rf ../../../www/themes/default
ln -s $ROOT/app/www/themes/default ../../../www/themes/default

# default scripts.

rm -rf ../../../www/share/atlantis
ln -s $ROOT/app/www/share/atlantis ../../../www/share/atlantis
