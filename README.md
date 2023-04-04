# Nether Atlantis

The most simple way to get going:

1. `git clone https://github.com/netherphp/project myapp`
2. `cd myapp`
3. `rm -rf .git`
4. `composer require netherphp/atlantis`
5. `atlantis init -y`

> Currently requires `minimum-stability: 'dev'` this project and its libraries are quite early on in their refresh. This is already taken into account if bootstrapped from the `project` repo.

> The `atlantis` command works because we add `./vendor/bin` to our shell PATH. Else you'd need to be calling it via `vendor/bin/atlantis`.

By default the default theme and shared resources will be copied into the public web dir. To recopy them from the package after updates:

6. `atlantis setup-theme`
7. `atlantis setup-share`

To convert the default theme and shared resources into a symlink so that updates seem more magic:

6. `atlantis setup-theme --link`
7. `atlantis setup-share --link`



# Default Project Structure

Running the `init` command will generate a project in the current directory with the following default structure.

### `/conf/config.json`

Global project configuration. Things that need to be shared between all environments like app name name and the like go in here.

### `/conf/env/dev/config.json`

Environment specific configuration. The environment is defined by an `env.lock` file in the `ProjectRoot`. The default value is `dev` which will then cause this config file to be applied after loading the global config.

### `/core/Local`

Default namespace for autoloading. You can immediately start creating classes here like `Local\Whatever` as `core/Local/Whatever.php` for your app and they will be loadable.

### `/routes/Home.php`

Default homepage route handler as an example.

### `/www/index.php`

Default route handler. This should be pretty decent for most projects you probably should not need to be adding things here.

### `/www/themes/default`

Default web theme so that the example looks cool out of the box. By default this theme is configured to be the bottom of the theme stack.

### `/www/themes/local`

Default local theme. Any area files you create here will overwrite requests to theme files from the default theme. Surface uses a Theme Stack so you can configure multiple themes to fall through until a template is found. The default is that it will check `local` first before falling back to checking `default` for area files.

### `/www/share/atlantis`

Shared resources that Atlantis' front-end depends on.



# Generating A Static Route Map

By default when you hit your project it will scan the `routes` directory and figure out what needs to happen on the fly. This is good for quick devving but to make it faster for production you can generate a static route file.

`netherave gen routes`

This will create a `routes.phson` file in the `ProjectRoot` which the router will then use instead of directory scanning.



# Credits

The following libraries are used in this project via direct inclusion (aka copy paste) rather than installed via Composer:

* jQuery (DOM, https://jquery.com/)
* Bootstrap (Frontend framework, https://getbootstrap.com/)
* Squire (HTML editor core, https://github.com/fastmail/Squire)
* Editor.js (Block editor, https://editorjs.io)
* SimpleLightbox (Image gallery thing, https://simplelightbox.com/)
