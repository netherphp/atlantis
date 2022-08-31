# Nether Atlantis

A project bootstrapper using Nether components.

1. `composer require netherphp/atlantis`
2. `vendor/bin/atlantis init`

**Note:** Currently requires `minimum-stability: 'dev'` this project and its
libraries are quite early on in their refresh.

# Default Project Structure

Running the `init` command will generate a project in the current directory
with the following default structure.

## `/conf/config.json`

Global project configuration.

## `/conf/env/dev/config.json`

Environment specific configuration. The environment is defined by an `env.lock`
file in the `ProjectRoot`. The default value is `dev` which will then cause
this config file to be applied after loading the global config.

## `/core/Local`

Default namespace for autoloading. You can immediately start creating classes
here like `Local\Whatever` for your app and they will be loadable.

## `/routes/Home.php`

Default homepage route handler as an example.

## `/www/.htaccess`

Default webroot configuration to route requests to our `index.php` router.
This probably only works for Apache based servers. If you are not using Apache
check out this file it should be clear how it works, it is very common, and
you can replicate it in your server of choice config.

## `/www/index.php`

Default route handler. This should be pretty decent for most projects you
probably should not need to be adding things here.

## `/www/themes/default`

Default web theme so that the example looks cool out of the box. By default
this theme is configured to be the bottom of the theme stack.

## `/www/themes/local`

Default local theme. Any area files you create here will overwrite requests
to theme files from the default theme. Surface uses a Theme Stack so you can
configure multiple themes to fall through until a template is found. The
default is that it will check `local` first before falling back to checking
`default` for area files.

# Generating A Static Route Map

By default when you hit your project it will scan the `routes` directory and
figure out what needs to happen on the fly. This is good for quick devving but
to make it faster for production you can generate a static route file.

`netherave gen routes`

This will create a `routes.phson` file in the `ProjectRoot` which the router
will then use instead of directory scanning.
