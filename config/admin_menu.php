<?php

// config/admin_menu.php
// เก็บ icon/route ของเมนูหลังบ้าน (static, ไม่เปลี่ยนบ่อย)
// `id` ต้องตรงกับ id ในตาราง admin_menus (DB เป็นคนกำหนดชื่อเมนู + เปิด/ปิดการแสดงผล)
// `key` ใช้อ้างอิงในโค้ด/debug เท่านั้น ไม่ผูกกับ DB

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
            ['key' => 'landing_page_structure', 'name' => 'โครงสร้างหน้าเว็บ', 'route' => null],
            ['key' => 'top_cover', 'name' => 'รูปปกบนสุดของเว็บ', 'route' => null],
            ['key' => 'infographic', 'name' => 'ภาพinfographic', 'route' => null],
        ],
    ],

    [
        'id' => 3,
        'key' => 'language',
        'icon' => 'bi-translate',
        'route' => null, // ยังไม่มี route จริง (รอ Phase 2)
    ],

    [
        'id' => 4,
        'key' => 'users',
        'icon' => 'bi-people',
        'route' => null, // ยังไม่มี route จริง (รอ Phase 1)
    ],

    [
        'id' => 5,
        'key' => 'settings',
        'icon' => 'bi-gear',
        'route' => null,
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
        'route' => null,
        'children' => [
            ['key' => 'admin_group', 'name' => 'กลุ่มผู้ใช้งาน', 'route' => null],
        ],
    ],

    [
        'id' => 11,
        'key' => 'about_us',
        'icon' => 'bi-send',
        'route' => null,
    ],

    [
        'id' => 12,
        'key' => 'business',
        'icon' => 'bi-send',
        'route' => null,
    ],

    [
        'id' => 13,
        'key' => 'document',
        'icon' => 'bi-send',
        'route' => null,
    ],

    [
        'id' => 14,
        'key' => 'work_us',
        'icon' => 'bi-send',
        'route' => null,
    ],

];
