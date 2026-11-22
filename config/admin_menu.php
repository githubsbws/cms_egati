<?php

// config/admin_menu.php
// เก็บ icon/route ของเมนูหลังบ้าน (static, ไม่เปลี่ยนบ่อย)
// `id` must match admin_menus.id (the DB decides active/inactive and the order).
// `key` is looked up in lang/{th,en}/admin/menuleft.php to get the label.
// id 3,4 เอาออกไมได้ใช้

return [

    [
        'id' => 1,
        'key' => 'home',
        'icon' => 'bi-house-door',
        'route' => '/admin',
    ],

    [
        'id' => 2,
        'key' => 'content',
        'icon' => 'bi-file-text',
        'route' => null,
        'children' => [
            ['key' => 'landing_page_structure', 'route' => null],
            ['key' => 'top_cover', 'route' => null],
            ['key' => 'infographic', 'route' => null],
        ],
    ],

    [
        'id' => 5,
        'key' => 'settings',
        'icon' => 'bi-gear',
        'route' => null,
        'children' => [
            ['key' => 'language', 'route' => null],
            ['key' => 'event_banner', 'route' => null],
            ['key' => 'cookie_consent', 'route' => null],
            // ['key' => 'seo_setting', 'route' => null],
        ],
    ],

    [
        'id' => 6,
        'key' => 'audit_log',
        'icon' => 'bi-clock-history',
        'route' => null,
    ],

    [
        'id' => 7,
        'key' => 'procurement',
        'icon' => 'bi-briefcase',
        'route' => null,
    ],

    [
        'id' => 8,
        'key' => 'news',
        'icon' => 'bi-newspaper',
        'route' => null,
        'children' => [
            // 'active' lists every route name that should highlight the item (create/edit pages too)
            ['key' => 'news_list', 'route' => 'admin.news.index', 'active' => ['admin.news.index', 'admin.news.create', 'admin.news.edit']],
            ['key' => 'news_category', 'route' => 'admin.news.category.index', 'active' => ['admin.news.category.*']],
        ],
    ],

    [
        'id' => 9,
        'key' => 'contact_us',
        'icon' => 'bi-send',
        'route' => null,
    ],

    [
        'id' => 10,
        'key' => 'permission',
        'icon' => 'bi-shield-lock',
        'route' => 'null',
        'children' => [
            ['key' => 'admin_group', 'route' => 'admin.permission.group'],
            ['key' => 'admin_list', 'route' => 'admin.permission.user'],
        ],
    ],

    [
        'id' => 11,
        'key' => 'about_us',
        'icon' => 'bi-info-circle',
        'route' => null,
    ],

    [
        'id' => 12,
        'key' => 'business',
        'icon' => 'bi-graph-up',
        'route' => null,
    ],

    [
        'id' => 13,
        'key' => 'document',
        'icon' => 'bi-file-earmark-arrow-up',
        'route' => null,
    ],

    [
        'id' => 14,
        'key' => 'work_us',
        'icon' => 'bi-person-arms-up',
        'route' => null,
    ],

    [
        'id' => 15,
        'key' => 'organization',
        'icon' => 'bi-building',
        'route' => null,
        'children' => [
            ['key' => 'management_team', 'route' => null],
            ['key' => 'director_board', 'route' => null],
            ['key' => 'orgchart', 'route' => null],
        ],
    ],

];
