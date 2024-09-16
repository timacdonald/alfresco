<?php

namespace Alfresco;

use Alfresco\Website\Title;
use Illuminate\Support\Collection;

return fn (
    ComponentFactory $render,
    Collection $items,
    Title $active,
    Collection $empty,
) => $items->map(fn (Title $title) => $title->children->isEmpty()
    ? ($empty->contains(fn (Title $empty) => $title->is($empty)) ? '' : $render->tag(
        as: 'li',
        attributes: [
            'class' => 'pl-4 py-1.5',
        ],
        before: $render->tag(
            as: 'a',
            attributes: [
                'class' => [
                    'hover:underline text-sm relative flex items-center',
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
                'class' => [
                    'cursor-pointer py-1.5 list-outside text-violet-950 text-sm',
                    $title->isOrHasChild($active) ? 'marker:text-violet-700' : 'marker:text-violet-300',
                ],
            ],
            before: $title->html,
        ),
        after: ($empty->contains(fn (Title $empty) => $title->is($empty))
            ? ''
            : $render->tag(
                as: 'li',
                attributes: [
                    'class' => 'pl-4 py-1.5',
                ],
                before: $render->tag(
                    as: 'a',
                    attributes: [
                        'class' => [
                            'hover:underline text-sm relative flex items-center',
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
