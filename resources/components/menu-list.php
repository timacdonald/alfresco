<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Website\Title;
use Alfresco\Render\Factory;
use Illuminate\Support\Collection;

return fn (
    Factory $render,
    Collection $items,
    Title $active,
    Collection $empty,
) => $items->map(fn (Title $title) => $title->children->isEmpty()
    ? ($empty->contains(fn (Title $empty) => $title->is($empty)) ? '' : $render->tag(
        as: 'div',
        attributes: [
            'class' => [
                'relative flex items-center pl-4 py-1.5 before:block before:h-1.5 before:w-1.5 before:rounded-full before:absolute before:left-[3px]',
                $title->is($active) ? 'before:bg-violet-700' : 'before:bg-violet-300',
            ],
        ],
        before: $render->tag(
            as: 'a',
            attributes: [
                'class' => [
                    'text-sm relative flex items-center',
                    $title->is($active) ? 'text-violet-700 font-bold' : 'text-violet-950',
                ],
                'href' => "{$title->id}.html",
            ],
            before: $title->html,
        )
    )->toString())
    : $render->tag(
        as: 'details',
        attributes: [
            'class' => 'pl-4',
            'open' => $title->isOrHasChild($active),
        ],
        before: $render->tag(
            as: 'summary',
            attributes: [
                'class' => 'cursor-pointer py-1.5 list-outside text-violet-950 text-sm marker:text-violet-300',
            ],
            before: $title->html,
        ),
        after: ($empty->contains(fn (Title $empty) => $title->is($empty))
            ? ''
            : $render->tag(
                as: 'div',
                attributes: [
                    'class' => [
                        'relative flex items-center pl-4 py-1.5 before:block before:h-1.5 before:w-1.5 before:rounded-full before:absolute before:left-[3px]',
                        $title->is($active) ? 'before:bg-violet-700' : 'before:bg-violet-300',
                    ],
                ],
                before: $render->tag(
                    as: 'a',
                    attributes: [
                        'class' => [
                            'text-sm relative flex items-center',
                            $title->is($active) ? 'text-violet-700 font-bold' : 'text-violet-950',
                        ],
                        'href' => "{$title->id}.html",
                    ],
                    before: $title->html.' overview',
                ),
            )).$render->component('menu-list', [
                'items' => $title->children,
                'active' => $active,
                'empty' => $empty,
            ]),
    )->toString())->join('');
