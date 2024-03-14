# **Atlantis** (NetherPHP WebApp)

[![Packagist](https://img.shields.io/packagist/v/netherphp/atlantis.svg?style=for-the-badge)](https://packagist.org/packages/netherphp/atlantis)
[![Build Status](https://img.shields.io/github/actions/workflow/status/netherphp/atlantis/phpunit.yml?style=for-the-badge)](https://github.com/netherphp/atlantis/actions)
[![codecov](https://img.shields.io/codecov/c/gh/netherphp/atlantis?style=for-the-badge&token=VQC48XNBS2)](https://codecov.io/gh/netherphp/atlantis)

Standards non-compliant website framework.

These are the open source components of our system that we commit back despite
nobody needing any of it. If you are using it I am more than happy to interact
but I am done pretending that I want a huge community for now while we are
constantly building, trying, refactoring, and re-imagining.

The framework itself will present completed documentation once you get it
running, so we keep a vanilla copy running on our intranet for reference lol.



# **Quick Setup**

**Clone the project Bootstrapper.**

* git clone git@github.com:netherphp/project app
* cd app
* rm -rf .git

**Initialise System**

* composer require netherphp/atlantis dev-master

* project.atl init
* project.atl setup

* web.atl config
* web.atl setup
* web.atl reload

**Database**

Add Default to config.php.

* `sh vendor/netherphp/atlantis/tables.sh`

**SSL?**

* `ssl.atl help`




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
