<?php

// config/careers.php
// Recruitment notices (admin menu id 14, website /careers).

return [

    // Largest PDF accepted for a notice document, in KB
    'max_document_kb' => 20480,

    // Contact shown on the careers pages
    'hr_email' => 'egati-hr@egati.co.th',
    'source_url' => 'https://egati.co.th/th/recruit/',
    'hr_people' => [
        ['key' => 'careers.human_resources_section_manager', 'name' => 'careers.hr_person_1', 'phones' => ['02-436-6944', '081-889-2238']],
        ['key' => 'careers.human_resources_officer', 'name' => 'careers.hr_person_2', 'phones' => ['02-436-6944', '080-042-2141']],
    ],

];
