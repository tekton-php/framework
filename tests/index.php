<?php require '../vendor/autoload.php';

use Tekton\Framework;

$src = __DIR__.DS.'..'.DS.'src';

$framework = Framework::getInstance();
$framework->setContainer(new \DI\Container)
          ->setEnvironment('production')
          ->setResourceCaching(true)
          ->setCacheDir(__DIR__.DS.'cache')
          ->setFacadeAliases('Tekton\\Facades', $src.DS.'Facades')
          ->registerAlias('Config', 'Tekton\\Facades\\Config')
          ->registerConfig(__DIR__.DS.'config')
          ->loadEnv()
          ->loadConfig()
          ->loadResources()
          ->loadAliases()
          ->loadProviders();


// $framework->registerProvider(new \Tekton\TestServiceProvider);
// $framework->registerProvider(\Tekton\TestTwoServiceProvider::class);

$framework->init(__DIR__, 'http://localhost:8000/');

echo Config::get('app.foo');
echo App::get('config')->get('app.foo');
