<?php declare(strict_types=1);

use Shopware\Core\Framework\Plugin\KernelPluginLoader\ComposerPluginLoader;
use Shopware\Core\HttpKernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

if (\PHP_VERSION_ID < 70403) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . \PHP_VERSION . ' but Shopware 6 requires at least PHP 7.4.3';
    exit(1);
}

$classLoader = require __DIR__ . '/../vendor/autoload.php';

$projectRoot = dirname(__DIR__);
if (class_exists(Dotenv::class) && (file_exists($projectRoot . '/.env.local.php') || file_exists($projectRoot . '/.env') || file_exists($projectRoot . '/.env.dist'))) {
    (new Dotenv())->usePutenv()->bootEnv(dirname(__DIR__) . '/.env');
}

$appEnv = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ($appEnv !== 'prod'));

if ($debug) {
    umask(0000);

    Debug::enable();
}

$trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false;
if ($trustedProxies) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}

$trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false;
if ($trustedHosts) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$request = Request::createFromGlobals();

$kernel = new HttpKernel($appEnv, $debug, $classLoader);
$kernel->setPluginLoader(new ComposerPluginLoader($classLoader));

$result = $kernel->handle($request);

$result->getResponse()->send();

$kernel->terminate($result->getRequest(), $result->getResponse());
