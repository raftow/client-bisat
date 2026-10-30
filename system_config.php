<?php
return [ 
    'db_prefix' => 'bisat_',
    'theme' => 'modern',
    'main_company_domain' => "bisat.com",
    'main_company' => "bisat",
    'amd_options' => [
            'tu_college' => 'one',
    ],
    'site_administrator' => 'Rafik BOUBAKER',
    'site_administrator_extension' => '0000',
    'site_administrator_email' => 'roubaker@bisat.com',

    'cust_type_list' => array(
        1 => ['ar' => "فرد من المجتمع",     'en' => "Community member",    'code' => "community",   'canBecome' => [3, 7, 6, 8, 5]],

        3 => ['ar' => "متدرب في كلية تقنية أو معهد صناعي",   'en' => "Government sector trainee",    'code' => "trainee-gov",   'canBecome' => [7, 6, 8, 5, 1]],
        7 => ['ar' => "متدرب في كلية تقنية عالمية", 'en' => "Colleges of Excellence trainee",    'code' => "trainee-coe",   'canBecome' => [3,  6, 8, 5, 1]],
        8 => ['ar' => "متدرب في معهد شراكات استراتيجية",  'en' => "Strategic partnerships trainee",    'code' => "trainee-sp",   'canBecome' => [3, 7, 6,  5, 1]],
        6 => ['ar' => "متدرب في معهد تدريب أهلي",    'en' => "Private sector trainee",     'code' => "trainee-pt",   'canBecome' => [3, 7,  8, 5, 1]],

        4 => ['ar' => "مدرب",                'en' => "Trainer",     'code' => "trainer",  'canBecome' => [2, 5, 1]],
        2 => ['ar' => "موظف",                'en' => "Employee",     'code' => "employee",   'canBecome' => [4, 5, 1]],
        5 => ['ar' => "متعاون من الخارج",   'en' => "External collaborator",     'code' => "collaborator",   'canBecome' => [2, 4, 1]],
    ),


    'cust_type_logic' => array(
        3 => [
            'ref_num' => ['title_ar' => 'الرقم التدريبي',    'title_en' => 'Academic number',],
            'org_name' => ['title_ar' => 'اسم الكلية أو المعهد', 'title_en' => 'Training unit',]
        ],
        /*
        7 => [
            'ref_num' => ['title_ar' => 'الرقم الأكاديمي',     'title_en' => 'Academic number',],
            'org_name' => ['title_ar' => 'اسم الكلية',         'title_en' => 'College name',]
        ],

        8 => [
            'ref_num' => ['title_ar' => 'الرقم الأكاديمي',     'title_en' => 'Academic number',],
            'org_name' => ['title_ar' => 'اسم المعهد أو الأكاديمية', 'title_en' => 'Name of the institute or academy',]
        ],*/

        2 => [
            'ref_num' => ['title_ar' => 'رقم الحاسب',       'title_en' => 'Employee number',],
            'org_name' => ['title_ar' => 'جهة العمل / الإدارة', 'title_en' => 'Employer / Department',]
        ],

        4 => [
            'ref_num' => ['title_ar' => 'رقم الحاسب',          'title_en' => 'Computer number',],
            'org_name' => ['title_ar' => 'الوحدة التدريبية',  'title_en' => 'Training unit',]
        ],

        5 => [
            'ref_num' => ['title_ar' => 'رقم الحاسب',          'title_en' => 'Computer number',],
            'org_name' => ['title_ar' => 'جهة العمل / الإدارة', 'title_en' => 'Employer / Department',]
        ],
    ),
];