#!/bin/sh

# demo router and applications.
cp ../../../routes/Docs.php app/routes/
cp ../../../routes/Home.php app/routes/

# demo themes.
cp -r ../../../www/themes/default/. app/www/themes/default
cp -r ../../../www/themes/local/. app/www/themes/local


