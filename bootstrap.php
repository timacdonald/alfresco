<?php

use Alfresco\Configuration;
use Illuminate\Container\Container;
use Spatie\ShikiPhp\Shiki;

return tap(Container::getInstance(), function (Container $container) {
    $container->singleton(Configuration::class, fn () => new Configuration([
        'debug' => false,
        'language' => 'en',
        'root_directory' => __DIR__,
        'build_directory' => __DIR__.'/build/output',
        'resource_directory' => __DIR__.'/resources',
        'index_directory' => __DIR__.'/build/indexes',
        'component_directory' => __DIR__.'/resources/components',
        'translation_directory' => __DIR__.'/resources/translations',
        'replacements_directory' => __DIR__.'/resources/replacements',
    ]));

    $container->bind(Shiki::class, fn (Container $container) => new Shiki(
        $container->make(Configuration::class)->get('root_directory').'/theme.json'
    ));
});
