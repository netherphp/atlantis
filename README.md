# **Atlantis** (NetherPHP WebApp)

[![Packagist](https://img.shields.io/packagist/v/netherphp/atlantis.svg?style=for-the-badge)](https://packagist.org/packages/netherphp/atlantis)
[![Build Status](https://img.shields.io/github/actions/workflow/status/netherphp/atlantis/phpunit.yml?style=for-the-badge)](https://github.com/netherphp/atlantis/actions)
[![codecov](https://img.shields.io/codecov/c/gh/netherphp/atlantis?style=for-the-badge&token=VQC48XNBS2)](https://codecov.io/gh/netherphp/atlantis)

Standards non-compliant website framework.

----
----
----
----

## **NOTES FOR README UPDATE LATER**

### **Install**

* `git clone git@github.com:netherphp/project app`
* `cd app`
* `rm -rf .git`
* `composer require netherphp/atlantis dev-master`
* `composer update`

### **Setup**

The following commands bootstrap the rest of this project directory structures
and generate the SSL cert configs.

* `dev.atl init`
* `ssl.atl setup`

### **Sanity Checks**

The following commands can be re-run to re-configure things using the values
from the previous setup to breeze through them.

* `dev.atl setup`
* `ssl.atl setup`

----
----
----
----

## **PREAMBLE**

Currently requires `minimum-stability: 'dev'` as this project and libraries are quite early in their refresh. There is a project bootstrapping repository to minimise the effort to get started at this stage.



## **POST-PREAMBLE**

The `atlantis` command works because we add `./vendor/bin` to our shell PATH. Add `bin` and `vendor/bin` to `PATH` so we can use library installed and custom built apps locally. Else call it via `vendor/bin/atlantis`.

**Not-Windows:**
```bash
export PATH=$PATH:./bin:./vendor/bin
```

**Not-Windows (Forever):**
* Usually by adding this stuff to the end of `.bashrc` or `.bash_profile` - I can never remember and I swear it changes every other install which one Ubuntu prefer today.

**Windows:**
```bat
set PATH=%PATH%;.\bin;.\vendor\bin
```

**Windows (Forever):**
* Hit the Windows key, type `environ`, and hit enter.
* Click `Environment Variables` button at bottom of window that opens.
* Use that window's little window to add the paths to your user's PATH.



## **INSTALL AND SETUP**

This is the quickest way to get it going with the way it is right now.

1. `git clone https://github.com/netherphp/project MyApp`
2. `cd MyApp`
3. `rm -rf .git`
4. `composer require netherphp/atlantis`
5. `atlantis init -y`
6. `atlantis setup`



## **PROJECT STRUCTURE OVERVIEW**

Running the `init` command will generate a project in the current directory with the following default structure:

### **CONFIGURATION**

* `/atlantis.json`

  Mostly contains low level configuration for setup and checking of the server, things the app does not need to know except once in forever.

* `/conf/config.json`

  Global project configuration. Things that need to be shared between all environments like app name go here.

* `/conf/env/dev/config.json`

  Environment specific configuration. The environment is defined by an `env.lock` file in the `ProjectRoot`. The default value is `dev` causing this config file to be applied after loading the global config.

### **CORE APP STUFF**

* `/core/Local`

  Default namespace for autoloading. You can immediately start creating classes here like `Local\Whatever` as `core/Local/Whatever.php` for your app and they will be loadable.

* `/routes/Home.php`

  Default homepage route handler as an example.

* `/www/index.php`

  Default router application for the webserver to funnel requests into. Should not technically need to be edited.

* `/www/themes/default`

  Default web theme for the application.

* `/www/themes/local`

  Local web theme for the application. Overrides anything from the default theme without having to edit the default theme, if the structure is mimicked.

* `/www/share/atlantis`

  Shared resources that the front-end depends on.

* `/www/share/nui`

  Shared more resources that the front-end depends on.



## **PERFORMANCE: STATIC ROUTE MAP**

By default the project will scan the `routes` directory and generate a route map on the fly. Good for teh quick dev but loading a pre-compiled static route map is much faster. These are kept as a `routes.phson` file in the project root. If it exists it will be loaded.

Any time application routing changes such as a Domain, Path, Verb, or Method Arguments, the static route file will need to be recompiled.

```shell
$ nave gen --atlantis
```



## **ENVIRONMENT CONFIGURATION**

By default the framework will run inself an env called `dev` which will then load the configuration info from that directory. The environment can be changed using the lock file or environment variable. The order of selection is:

1) Value of `$_ENV['ATLANTIS.ENV']`.
2) Contents of `env.lock` file in the project root.
3) Fallback to `dev`.

The environments can also be classified with a prefix. For example an ENV name of `dev-apache` will report as a `dev` class environment.

The `atlantis env` command will write to the `env.lock` file when using the `--set` option.



## **SSL NOTES**

SSL info should be put inside the `conf/env/<env>/config.php` file. An AcmePHP YML file can then be generated:

```sh
$ atlantis acmephp-config
```

And then the cert can be fetched and/or renewed:

```sh
$ atlantis-ssl renew
```

There is a tool that can generate a `crontab` entry to automate SSL renewals.

```sh
$ atlantis-cron ssl

Crontab: 20 4 * * * env php /opt/sar-dev/vendor/bin/atlantis-ssl renew
Runs At: 2023-07-10 23:20:00 CDT (in 6hr 47min)
```

There is a tool that can check if it thinks the cron automation will work.

```sh
$ atlantis-cron list

Current Time: 2023-07-10 16:34:35 CDT
Crontab Entries: 12
Atlantis SSL Entry: OK

[...dump of all cron items...]
```

There is a tool that can tell you about SSL for any domain. It also has a `--json` option to spit out in JSON format instead of human readable.

```sh
$ atlantis-ssl check pegasusgate.net

Domain: PEGASUSGATE.NET
Status: OK
Code: 1
Date: 2023-06-17
ExpireDate: 2023-09-15
ExpireTimeframe: 2m 5d
Source: OpenSSL
```



# **CREDITS**

The following libraries are used in this project via direct inclusion and are self managed rather than installed via Composer:

### Scripting

* jQuery (DOM, https://jquery.com/)
* Squire (HTML editor core, https://github.com/fastmail/Squire)
* SimpleLightbox (Image gallery thing, https://github.com/andreknieriem/simplelightbox)
* Luxon (DateTime stuff, https://github.com/moment/luxon)
* Editor.js (Block editor, https://github.com/codex-team/editor.js)

### Style & Assets

* Bootstrap (Frontend framework, https://getbootstrap.com)
* Material Design Icons (Icons: UI, https://pictogrammers.com/library/mdi)
* Simple Icons Font (Icons: Brands, https://github.com/simple-icons/simple-icons-font)
