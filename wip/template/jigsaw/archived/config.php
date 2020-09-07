<?php

use Illuminate\Support\Str;
use Sanity\Client;
use Sanity\BlockContent;

return [
    'baseUrl' => '',
    'production' => false,
    'siteName' => 'Blog Starter Template',
    'siteDescription' => 'Generate an elegant blog with Jigsaw and Sanity',
    'siteAuthor' => 'Author Name',

    // collections
    'collections' => [

        'posts' => [
            'extends' => '_layouts.post',
            'items' => function ($config) {
                $client = new Client([
                    'projectId' => '8h1u84cx',
                    'dataset' => 'production',
                    'useCdn' => true,
                    'apiVersion' => (new Datetime)->format('Y-m-d'),
                ]);

                $posts = $client->fetch(
                    '*[_type == "post"]{categories[]->{title},author->{name},"cover_image_url": cover_image.asset->url,...}'
                );

                return collect($posts)->map(function ($post) {

                    return [
                        'title' => $post['title'],
                        'content' =>
                            BlockContent::toHtml($post['body'], [
                                'projectId' => '8h1u84cx',
                                'dataset' => 'production',
                            ]),
                        'categories' => collect($post['categories'])->map(fn($category) => $category['title']),
                        'date' => Datetime::createFromFormat('Y-m-d', $post['date'])->getTimestamp(),
                        'active' => true,
                        'slug' => $post['slug']['current'],
                        'path' => '{slug}',
                        'author' => $post['author']['name'],
                        'cover_image' => $post['cover_image_url'],
                        'featured' => true,
                    ];
                });
            },
            'getExcerpt' => function ($page, $length = 255) {
                if ($page->excerpt) {
                    return $page->excerpt;
                }

                $content = preg_split('/<!-- more -->/m', $page->getContent(), 2);
                $cleaned = trim(
                    strip_tags(
                        preg_replace(['/<pre>[\w\W]*?<\/pre>/', '/<h\d>[\w\W]*?<\/h\d>/'], '', $content[0]),
                        '<code>'
                    )
                );

                if (count($content) > 1) {
                    return $cleaned;
                }

                $truncated = substr($cleaned, 0, $length);

                if (substr_count($truncated, '<code>') > substr_count($truncated, '</code>')) {
                    $truncated .= '</code>';
                }

                return strlen($cleaned) > $length
                    ? preg_replace('/\s+?(\S+)?$/', '', $truncated) . '...'
                    : $cleaned;
            },
            'isActive' => function ($page, $path) {
                return true;
                return Str::endsWith(trimPath($page->getPath()), trimPath($path));
            },
            'getDate' => function ($page) {
                return Datetime::createFromFormat('U', $page->date);
            },
        ],

//        'posts' => [
//            'author' => 'Author Name', // Default author, if not provided in a post
//            'sort' => '-date',
//            'path' => 'blog/{filename}',
//        ],
//        'categories' => [
//            'path' => '/blog/categories/{filename}',
//             ],
//
        // helpers
        'getDate' => function ($page) {
            return Datetime::createFromFormat('U', $page->date);
        },
        'getExcerpt' => function ($page, $length = 255) {
            if ($page->excerpt) {
                return $page->excerpt;
            }

            $content = preg_split('/<!-- more -->/m', $page->getContent(), 2);
            $cleaned = trim(
                strip_tags(
                    preg_replace(['/<pre>[\w\W]*?<\/pre>/', '/<h\d>[\w\W]*?<\/h\d>/'], '', $content[0]),
                    '<code>'
                )
            );

            if (count($content) > 1) {
                return $cleaned;
            }

            $truncated = substr($cleaned, 0, $length);

            if (substr_count($truncated, '<code>') > substr_count($truncated, '</code>')) {
                $truncated .= '</code>';
            }

            return strlen($cleaned) > $length
                ? preg_replace('/\s+?(\S+)?$/', '', $truncated) . '...'
                : $cleaned;
        },
        'isActive' => function ($page, $path) {
            return Str::endsWith(trimPath($page->getPath()), trimPath($path));
        },
    ]
];
