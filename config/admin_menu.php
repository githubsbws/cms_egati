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
            ['key' => 'hero_video', 'route' => 'admin.content.hero.edit', 'active' => ['admin.content.hero.*']],
            ['key' => 'about_story', 'route' => 'admin.content.story.edit', 'active' => ['admin.content.story.*']],
            ['key' => 'video_presentation', 'route' => 'admin.content.presentation.edit', 'active' => ['admin.content.presentation.*']],
            ['key' => 'related_agencies', 'route' => 'admin.content.agencies.index', 'active' => ['admin.content.agencies.*']],
        ],
    ],

    [
        'id' => 5,
        'key' => 'settings',
        'icon' => 'bi-gear',
        'route' => null,
        'children' => [
            ['key' => 'language', 'route' => 'admin.settings.language.index', 'active' => ['admin.settings.language.*']],
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
        'children' => [
            ['key' => 'procurement_plan', 'route' => 'admin.procurement.plan.index', 'active' => ['admin.procurement.plan.*']],
            ['key' => 'procurement_price', 'route' => 'admin.procurement.price.index', 'active' => ['admin.procurement.price.*']],
            ['key' => 'procurement_notice', 'route' => 'admin.procurement.notice.index', 'active' => ['admin.procurement.notice.*']],
            ['key' => 'procurement_winner_in', 'route' => 'admin.procurement.winner_in.index', 'active' => ['admin.procurement.winner_in.*']],
            ['key' => 'procurement_winner_out', 'route' => 'admin.procurement.winner_out.index', 'active' => ['admin.procurement.winner_out.*']],
            ['key' => 'procurement_agency', 'route' => 'admin.procurement.agency.index', 'active' => ['admin.procurement.agency.*']],
            ['key' => 'procurement_work_type', 'route' => 'admin.procurement.work_type.index', 'active' => ['admin.procurement.work_type.*']],
            ['key' => 'procurement_method', 'route' => 'admin.procurement.method.index', 'active' => ['admin.procurement.method.*']],
        ],
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
        'children' => [
            ['key' => 'document_credit', 'route' => 'admin.documents.credit.index', 'active' => ['admin.documents.credit.*']],
            ['key' => 'document_finance', 'route' => 'admin.documents.finance.index', 'active' => ['admin.documents.finance.*']],
            ['key' => 'document_annual', 'route' => 'admin.documents.annual.index', 'active' => ['admin.documents.annual.*']],
        ],
    ],

    [
        'id' => 14,
        'key' => 'work_us',
        'icon' => 'bi-person-arms-up',
        'route' => null,
        'children' => [
            ['key' => 'career_jobs', 'route' => 'admin.careers.jobs.index', 'active' => ['admin.careers.jobs.*', 'admin.careers.documents.*']],
            ['key' => 'career_categories', 'route' => 'admin.careers.categories.index', 'active' => ['admin.careers.categories.*']],
        ],
    ],

    [
        'id' => 15,
        'key' => 'organization',
        'icon' => 'bi-building',
        'route' => null,
        'children' => [
            ['key' => 'management_team', 'route' => 'admin.organization.management.index', 'active' => ['admin.organization.management.*']],
            ['key' => 'director_board', 'route' => 'admin.organization.board.index', 'active' => ['admin.organization.board.*']],
            ['key' => 'orgchart', 'route' => 'admin.organization.chart.index', 'active' => ['admin.organization.chart.*']],
        ],
    ],

];
