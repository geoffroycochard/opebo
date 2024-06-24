<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config
set('repository', 'git@github.com:geoffroycochard/opebo.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Hosts
host('staging.vom')
    ->set('hostname', '195.154.186.13')
    ->set('remote_user', 'ADMIN-OPE')
    ->set('deploy_path', '/var/www/html/ORLEANS/ADMIN-OPE/dev-admin-ope.orleans.fr/Root-SYMFONY')
    ->setForwardAgent(true)
;

// Hooks
after('deploy:failed', 'deploy:unlock');

task('restart:php-fpm', function () {
    run('sudo systemctl restart php8.2-fpm.service');
});

// after('deploy:symlink', 'restart:php-fpm');

task('test', function () {
    run('cd /var/www/html/ORLEANS/ORLEANS-METROPOLE/dev.orleans-metropole.fr/Root-TYPO3/current 2>/dev/null || cd /var/www/html/ORLEANS/ORLEANS-METROPOLE/dev.orleans-metropole.fr/Root-TYPO3;echo "yo";');
});