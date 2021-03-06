<?php

namespace phpDocumentor\Plugin;

use phpDocumentor\Configuration as ApplicationConfiguration;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        /** @var ApplicationConfiguration $config */
        $config = $app['config'];
        $plugins = $config->getPlugins();

        if (! $plugins) {
            $app->register(new Core\ServiceProvider());
            $app->register(new Scrybe\ServiceProvider());

            return;
        }

        array_walk(
            $plugins,
            function ($plugin) use ($app) {
                /** @var Plugin $plugin */
                $provider = (strpos($plugin->getClassName(), '\\') === false)
                    ? sprintf('phpDocumentor\\Plugin\\%s\\ServiceProvider', $plugin->getClassName())
                    : $plugin->getClassName();
                if (!class_exists($provider)) {
                    throw new \RuntimeException('Loading Service Provider for ' . $provider . ' failed.');
                }

                try {
                    $app->register(new $provider($plugin));
                } catch (\InvalidArgumentException $e) {
                    throw new \RuntimeException($e->getMessage());
                }
            }
        );
    }
}
