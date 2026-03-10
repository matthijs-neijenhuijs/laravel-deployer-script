# laravel-deployer-script

This repository contains a deploy script for Laravel projects.

## Fields in deploy.php

### Core settings

- `application`: Logical project name used by Deployer.
- `repository`: Git repository URL Deployer pulls code from.
- `git_tty`: Enables TTY during Git operations (useful for SSH auth prompts).

### Shared data

- `shared_files`: Files that must persist between releases. Currently empty.
- `shared_dirs`: Directories shared across releases to preserve runtime/user data.
	- `public/files`
	- `public/uploads`
	- `storage`
	- `public/filemanager`
	- `vendor`

### Host configuration

- `host('127.0.0.1')`: Target server definition (currently localhost).
- `remote_user`: SSH user for deployment (`deployer`).
- `setPort(55)`: SSH port.
- `deploy_path`: Base path on server where releases are stored (`/var/www/website.nl`).

### Deploy task pipeline

The `deploy` task defines the sequence that runs on each deployment:

- `deploy:prepare`: Prepare release directories and metadata.
- `deploy:vendors`: Install PHP dependencies.
- Laravel Artisan cache/link commands:
	- `artisan:storage:link`
	- `artisan:view:clear` and `artisan:view:cache`
	- `artisan:config:clear` and `artisan:config:cache`
	- `artisan:route:clear` and `artisan:route:cache`
	- `artisan:event:clear` and `artisan:event:cache`
- `artisan:migrate`: Run database migrations.
- `artisan:queue:restart`: Restart queue workers.
- `npm:install`: Install Node dependencies.
- `npm:run:build`: Build frontend assets (`npm run build`).
- `deploy:publish`: Switch symlink to new release and finalize.
- `php-fpm:reload`: Reload PHP-FPM so new code is served.
- final `artisan:route:clear`: Clears route cache at the end.

### Hooks

- `after('deploy:failed', 'deploy:unlock')`: Unlocks deploy state after a failed deploy.
- `before('deploy:symlink', 'artisan:migrate')`: Ensures migrations run before switching to the new release.
