# HEPTAconnect

> Playground repository for evaluation purposes

This project is intended to showcase HEPTAconnect and to help you evaluate it for your use-case.
[You can learn more about HEPTAconnect in the documentation.](https://www.heptaconnect.io/guides/playground/)

## Installation

```shell
composer create-project heptaconnect/playground
```

Configure the document root directory (`/public`) to be hosted by a webserver under a dedicated hostname.
If you are using macOS, we recommend [Laravel Herd](https://herd.laravel.com).

✅ That's it. The system installation is complete.

## Development

* You can install additional portals or portal extensions via composer.
    * Run `composer require niemand-online/heptaconnect-portal-amiibo`.
    * Run `bin/console heptaconnect:portal-node:add 'NiemandOnline\HeptaConnect\Portal\Amiibo\AmiiboPortal' amiibo`
* You can develop custom portals or portal extensions by adding them in the directory `/src/Portal`.
    * Create a new directory `/src/Portal/HelloWorld`.
    * Inside this new directory create a class `HeptaConnect\Production\Portal\HelloWorld\HelloWorldPortal` that extends `Heptacom\HeptaConnect\Portal\Base\Portal\Contract\PortalContract`.
    * Run `bin/console cache:clear`.
    * Run `bin/console heptaconnect:portal-node:add 'HeptaConnect\Production\Portal\HelloWorld\HelloWorldPortal' hello-world`.
    * [Read more about portal development in the documentation.](https://heptaconnect.io/guides/portal-developer/)
* You can create migrations to get reproducible database operations that run once per installation.
    * Run `bin/console database:create-migration` to generate a new migration file in `/src/Integration/Migration`.
    * The `\HeptaConnect\Production\Integration\Component\Migration\MigrationHelper` class provides convenience methods like `addPortalNode`, `addRoute` and `activatePortalExtension`.
    * You can use `\Heptacom\HeptaConnect\Storage\Base\Bridge\Contract\StorageFacadeInterface` via `$migrationHelper->getStorageFacade()`. This will grant you access to every storage action of the management storage.
    * You can use `\Doctrine\DBAL\Connection` via `$migrationHelper->getConnection()`. This will grant you direct access to the underlying database.
    * Run `bin/console system:update:finish` to apply all new migrations.
* You can normalize your code-style using `laravel/pint`.
    * Edit `pint.json` to customize your defined rules.
        * [Here is an overview of all available rules and their configuration options.](https://mlocati.github.io/php-cs-fixer-configurator/)
    * Run `composer cs:lint` to check your code against your defined rules.
    * Run `composer cs:fix` to automatically apply your defined rules to your code.

## Deployment

Your deployment strategy will influence the availability of your application and the amount of maintenance required during deployments.
Since any good deployment strategy is tailored to your specific requirements and circumstances, there is no universal solution.
So, instead of providing a complete deployment script, we provide a narrative of recommended steps.

* It is recommended to use some kind of CI/CD pipeline for your deployments. Some of the best known providers are:
    * [GitHub Actions](https://github.com/features/actions)
    * [GitLab CI/CD pipelines](https://docs.gitlab.com/ee/ci/pipelines/)
    * [Bitbucket Pipelines](https://bitbucket.org/product/features/pipelines)
* Run `composer install --no-dev` in your CI/CD pipeline.
    * Collect the files you want to deploy in an artifact.
* Stop all running cronjobs and message consumers on your target server(s).
    * If you are using [Supervisor](http://supervisord.org/), run `supervisorctl stop all`.
    * If you are using [Cron](https://de.wikipedia.org/wiki/Cron), run `crontab -r`.
* Copy your prepared artifact files to your target server(s).
* Also remember to delete files on your target server(s) that have been removed or renamed since your last deployment.
    * If you are using [rsync](https://rsync.samba.org/), use the option `--delete`.
    * ⚠️ Caution: Only apply deletions in the directories `/src` and `/vendor`! Other directories contain files that are custom for their environment and not part of your VCS.
* Run `bin/console cache:clear` on your target server(s) to clear the cache.
* Run `bin/console system:update:finish` on your target server(s) to apply database migrations.
* Finally, start your cronjobs and message consumers again.
