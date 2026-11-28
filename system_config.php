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
        1 => ['ar' => "مسافر",     'en' => "Traveler",    'code' => "Traveler",   'canBecome' => [3, 4, 5]],

        3 => ['ar' => "صاحب مركبات",   'en' => "Vehicle Owner",    'code' => "vehcl-owner",   'canBecome' => [1, 4, 5]],

        4 => ['ar' => "صاحب فنادق",   'en' => "Hotel Owner",    'code' => "hotel-owner",   'canBecome' => [1, 3, 5]],
        5 => ['ar' => "مكتب رحلات",   'en' => "Travel Agency",    'code' => "travel-agency",   'canBecome' => [1, 3, 4]],
    ),


    'note-privacy' => "عزيزي العميل، نود التأكيد على أن الحصول على اسمك ورقم الجوال وتاريخ ميلادك هو لغرض تسهيل إدارة حجوزاتك، كما نؤكد لك عنايتنا وحرصنا على خصوصية وأمن بياناتك",

    'cust_type_logic' => array(
        1 => [
            'ref_num' => ['title_ar' => 'رقم الهوية / الإقامة / جواز السفر',    'title_en' => 'ID / Residence Permit / Passport Number',],
        ],

        3 => [
            'ref_num' => ['title_ar' => 'السجل التجاري',    'title_en' => 'Trade number',],
            'org_name' => ['title_ar' => 'اسم الشركة', 'title_en' => 'Company name',]
        ],

        4 => [
            'ref_num' => ['title_ar' => 'السجل التجاري',    'title_en' => 'Trade number',],
            'org_name' => ['title_ar' => 'اسم الشركة', 'title_en' => 'Company name',]
        ],

        5 => [
            'ref_num' => ['title_ar' => 'السجل التجاري',    'title_en' => 'Trade number',],
            'org_name' => ['title_ar' => 'اسم الشركة', 'title_en' => 'Company name',]
        ],

    ),
];
