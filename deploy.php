<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/php-fpm.php';
require 'contrib/npm.php';

// Project namex
set('application', 'newdutchbridge.nl');

// Project repository
set('repository', 'matthijsneijenhuijs@bitbucket.org:matthijsneijenhuijs/laravelproject.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', ['public/files', 'public/uploads', 'storage', 'public/filemanager', 'vendor']);

// Hosts

// host('167.99.215.109')->user('deployer')
host('127.0.0.1')->set('remote_user', 'deployer')
    ->setPort(55)
    ->set('deploy_path', '/var/www/website.nl');

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'artisan:storage:link',
    'artisan:view:clear',
    'artisan:view:cache',
    'artisan:config:clear',
    'artisan:config:cache',
    'artisan:route:clear',
    'artisan:route:cache',
    'artisan:event:clear',
    'artisan:event:cache',
    'artisan:migrate',
    'artisan:queue:restart',
    'npm:install',
    'npm:run:build',
    'deploy:publish',
    'php-fpm:reload',
    'artisan:route:clear',
]);

task('npm:run:build', function () {
    cd('{{release_or_current_path}}');
    run('npm run build');
});

after('deploy:failed', 'deploy:unlock');

before('deploy:symlink', 'artisan:migrate');
