<?php

use Alfresco\AbstractComponentFactory;
use Alfresco\Configuration;
use Alfresco\Container;
use Alfresco\Highlighter;
use Alfresco\Manual;
use Alfresco\Output;
use Alfresco\Translation;
use Alfresco\TranslationFactory;
use Alfresco\Website\Generator as Website;
use Spatie\ShikiPhp\Shiki;

return new Container([
    Configuration::class => fn () => once(fn () => new Configuration([
        'generation' => true,
        'generators' => [Website::class => ['en']],
        'build_directory' => __DIR__.'/build/output',
        'resource_directory' => __DIR__.'/resources',
        'index' => true,
        'index_directory' => __DIR__.'/build/indexes',
        'output_ansi' => false,
        'debug' => false,
    ])),
    Manual::class => fn (Container $container) => with(
        XMLReader::open($container->make(Configuration::class)->get('manual_path'), 'UTF-8'),
        function (XMLReader|bool $reader) {
            if ($reader === false) {
                throw new RuntimeException('Unable to create XML reader.');
            }

            return new Manual($reader);
        }
    ),
    Highlighter::class => fn () => new Highlighter(new Shiki(__DIR__.'/theme.json')),
    Output::class => fn (Container $container) => once(fn () => new Output(function (string $message) {
        echo $message;
    }, $container->make(Configuration::class)->get('output_ansi'))),
    TranslationFactory::class => fn (Container $container) => new TranslationFactory(
        fn (string $language) => new Translation(
            $language,
            require $container->make(Configuration::class)->get('resource_directory')."/translations/{$language}.php"
        )),
    AbstractComponentFactory::class => fn (Container $container) => new AbstractComponentFactory(
        "{$container->make(Configuration::class)->get('resource_directory')}/components",
        $container->make(TranslationFactory::class),
    ),
]);
