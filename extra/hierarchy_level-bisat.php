<?php
$hierarchy_level = [];
/**
 * @var int $current_domain
 * @var int $current_module
 */
if (((!$current_domain) or ($current_domain == 25)) and  // admission
        ((!$current_module) or ($current_module == 1282))) {
    $hierarchy_level[50] = ['ar' => 'مدير التطبيق', 'en' => 'Application manager'];
    $hierarchy_level[100] = ['ar' => 'مدير الشركة', 'en' => 'Company manager'];
    $hierarchy_level[400] = ['ar' => 'مشرف سياحي', 'en' => 'Turism supervisor'];
    $hierarchy_level[999] = ['ar' => 'موظف التسويق', 'en' => 'Marketing employee'];
   
}
