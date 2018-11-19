<?php namespace Tekton\Contracts;

use Psr\Container\ContainerInterface;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container);

    public function boot(ContainerInterface $container);
}
