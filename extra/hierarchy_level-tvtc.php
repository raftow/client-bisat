<?php
$hierarchy_level = [];

if((!$current_domain) or ($current_domain == 25)) // admission
{
    $hierarchy_level[100]=['ar'=> 'مدير قبول', 'en'=> 'Admission director'];
    $hierarchy_level[400]=['ar'=> 'مشرف قبول', 'en'=> 'Admission supervisor'];
    $hierarchy_level[800]=['ar'=> 'موظف قبول', 'en'=> 'Admission employee'];
    $hierarchy_level[999]=['ar'=> 'موظف مراجعة وثائق', 'en'=> 'Document review employee'];
}

if((!$current_domain) or ($current_domain == 13)) // crm
{
    $hierarchy_level[100]=['ar'=> 'مدير خدمة العملاء', 'en'=> 'CRM director'];
    $hierarchy_level[400]=['ar'=> 'مشرف خدمة العملاء', 'en'=> 'CRM supervisor'];
    $hierarchy_level[800]=['ar'=> 'منسق خدمة العملاء', 'en'=> 'CRM investigator'];
    $hierarchy_level[999]=['ar'=> 'موظف خدمة العملاء', 'en'=> 'CRM employee'];    
}

