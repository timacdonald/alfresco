<?php

use Alfresco\ComponentFactory;
use Alfresco\Translation;
use Illuminate\Config\Repository as Configuration;
use Illuminate\Container\Container;
use Spatie\ShikiPhp\Shiki;

return tap(Container::getInstance(), function (Container $container) {
    $container->instance(Container::class, $container);

    $container->singleton(Configuration::class, fn () => new Configuration([
        'debug' => false,
        'language' => 'en',
        'root_directory' => __DIR__,
        'build_directory' => __DIR__.'/build/output',
        'cache_directory' => __DIR__.'/build/cache',
        'resource_directory' => __DIR__.'/resources',
        'index_directory' => __DIR__.'/build/indexes',
        'component_directory' => __DIR__.'/resources/components',
        'translation_directory' => __DIR__.'/resources/translations',
        'replacements_directory' => __DIR__.'/resources/replacements',
    ]));

    $container->bind(Shiki::class, fn (Container $container) => new Shiki(
        $container->make(Configuration::class)->get('root_directory').'/theme.json'
    ));

    $container->singleton(ComponentFactory::class);
    $container->singleton(Translation::class);
});
