<?php

// config/home_content.php
// Editable home page blocks. Each block lists its default media (under public/web-assets), the text fields
// (default = a web string key, or a literal text) and the non-translated fields (default value).
// Text types: 'text' and 'rich' (the default may contain html such as <br> or <span>; text saved in the back office is plain text, a new line becomes a line break).
// 'admin' => false keeps a field out of the back office form: it always shows its default.
// Plain types: 'url' (http(s) URL or a site path such as "business") and 'duration' (mm:ss or hh:mm:ss).

return [

    'max_video_kb' => 102400,
    'max_image_kb' => 5120,
    'max_logo_kb' => 2048,

    'sections' => [

        'hero' => [
            'menu_key' => 'hero_video',
            'blocks' => [
                'hero' => [
                    'video' => 'clean-energy-flood-mitigation.mp4',
                    'poster' => 'clean-energy-flood-mitigation-poster.jpg',
                    'texts' => [
                        'eyebrow' => ['admin' => false, 'key' => 'home.egat_international_energy_beyond_borders', 'type' => 'text'],
                        'heading' => ['key' => 'home.thailand_s_gateway_to_global', 'type' => 'rich'],
                        'paragraph' => ['key' => 'home.as_the_international_investment_arm', 'type' => 'rich', 'long' => true],
                        'primary_label' => ['key' => 'home.explore_our_business', 'type' => 'text'],
                        'secondary_label' => ['key' => 'home.discover_egati', 'type' => 'text'],
                        'caption_label' => ['admin' => false, 'text' => 'CLEAN ENERGY / FLOOD MITIGATION', 'type' => 'text'],
                        'caption_title' => ['admin' => false, 'key' => 'home.clean_energy_and_flood_mitigation_2', 'type' => 'text'],
                        'caption_link_label' => ['admin' => false, 'key' => 'home.watch_on_youtube', 'type' => 'text'],
                    ],
                    'plain' => [
                        'primary_url' => ['default' => 'business', 'type' => 'url'],
                        'secondary_url' => ['default' => 'about', 'type' => 'url'],
                        'caption_url' => ['admin' => false, 'default' => 'https://www.youtube.com/watch?v=oBXLjTpzpbY', 'type' => 'url'],
                    ],
                ],
            ],
        ],

        'story' => [
            'menu_key' => 'about_story',
            'blocks' => [
                'story_1' => [
                    'video' => 'about-hydro.mp4',
                    'poster' => 'about-hydro.jpg',
                    'texts' => [
                        'kicker' => ['admin' => false, 'text' => 'INTERNATIONAL ENERGY', 'type' => 'text'],
                        'title' => ['key' => 'about.beyond_borders_new_possibilities', 'type' => 'rich'],
                        'text' => ['key' => 'about.egat_international_company_limited_conducts', 'type' => 'text', 'long' => true],
                        'cta_label' => ['key' => 'about.discover_egati', 'type' => 'text'],
                        'chapter_label' => ['admin' => false, 'key' => 'about.beyond_borders', 'type' => 'text'],
                    ],
                    'plain' => [
                        'cta_url' => ['default' => 'about', 'type' => 'url'],
                    ],
                ],
                'story_2' => [
                    'video' => 'about-solar.mp4',
                    'poster' => 'about-solar.jpg',
                    'texts' => [
                        'kicker' => ['admin' => false, 'text' => 'ENERGY & OPPORTUNITY', 'type' => 'text'],
                        'title' => ['key' => 'about.different_energies_shared_possibilities', 'type' => 'rich'],
                        'text' => ['key' => 'about.from_hydropower_to_solar_energy', 'type' => 'text', 'long' => true],
                        'cta_label' => ['key' => 'about.explore_our_business', 'type' => 'text'],
                        'chapter_label' => ['admin' => false, 'key' => 'about.energy_opportunities', 'type' => 'text'],
                    ],
                    'plain' => [
                        'cta_url' => ['default' => 'business', 'type' => 'url'],
                    ],
                ],
                'story_3' => [
                    'video' => 'about-forest.mp4',
                    'poster' => 'about-forest.jpg',
                    'texts' => [
                        'kicker' => ['admin' => false, 'text' => 'GOVERNANCE & RESPONSIBILITY', 'type' => 'text'],
                        'title' => ['key' => 'about.building_trust_growing_together', 'type' => 'rich'],
                        'text' => ['key' => 'about.corporate_governance_and_social_responsibility', 'type' => 'text', 'long' => true],
                        'cta_label' => ['key' => 'about.corporate_governance', 'type' => 'text'],
                        'chapter_label' => ['admin' => false, 'key' => 'about.responsible_growth', 'type' => 'text'],
                    ],
                    'plain' => [
                        'cta_url' => ['default' => 'about/governance', 'type' => 'url'],
                    ],
                ],
            ],
        ],

        'presentation' => [
            'menu_key' => 'video_presentation',
            'blocks' => [
                'presentation' => [
                    'video' => 'egati-presentation-en.mp4',
                    'poster' => 'presentation-poster.png',
                    'texts' => [
                        'eyebrow' => ['admin' => false, 'text' => 'EGATi’S VIDEO PRESENTATION', 'type' => 'text'],
                        'title' => ['key' => 'presentation.our_story_through_the_lens', 'type' => 'rich'],
                        'text' => ['key' => 'presentation.get_to_know_egat_international', 'type' => 'text', 'long' => true],
                        'language_note' => ['admin' => false, 'key' => 'presentation.english_audio_english_subtitles', 'type' => 'text'],
                        'button_label' => ['admin' => false, 'key' => 'presentation.watch_presentation', 'type' => 'text'],
                    ],
                    'plain' => [
                        'duration' => ['default' => '03:15', 'type' => 'duration'],
                    ],
                ],
            ],
        ],

    ],

];
