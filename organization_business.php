<?php

class OrganizationBusiness
{
    /**
     * @param int $dept_id
     * @param int $main_deptid
     * @param string $deptname_arabic
     * @param string $deptname_english
     * @param string $create_date
     * @param string $update_date
     * @param string $std_dpt
     * @param string $status
     * @param string $mgr_email
     */

    public static function create_or_update_tvtc_dept(
        $dept_id,
        $deptname_arabic,
        $deptname_english,
        $main_deptid,
        $create_date,
        $update_date,
        $std_dpt,
        $status,
        $mgr_email
    ) {
        $exists = 0; // AfwDatabase::db_recup_value("select count(*) as nb from tvtc_hrm.tvtc_dept where DEPT_ID = $dept_id");

        if ($exists == 1) {
            $set_dept_id = "";
            $where_dept_id = "DEPT_ID = '$dept_id'";
            $sql_action = "update";
        } else {
            $set_dept_id = "DEPT_ID = '$dept_id',";
            $where_dept_id = "";
            $sql_action = "insert into";
        }


        $deptname_arabic = addslashes($deptname_arabic);
        $deptname_english = addslashes($deptname_english);

        $sql_sentence = "$sql_action tvtc_hrm.tvtc_dept set $set_dept_id
  DeptNameArabic = _utf8'$deptname_arabic',
  DeptNameEnglish = '$deptname_english',
  MAIN_DEPTID = '$main_deptid',
  CREATE_DATE = '$create_date',
  UPDATE_DATE = '$update_date',
  STD_DPT = '$std_dpt',
  STATUS = '$status',
  MGR_EMAIL = '$mgr_email'
  $where_dept_id
  ";

        AfwDatabase::db_query($sql_sentence);
    }


    public static function get_all_dept_from_api() {
        $url = "https://commws.tvtc.gov.sa/RestService/get_all_dept";
        $data = null;
        $proxy = "hq-proxy|8080";
        $return = AfwApiConsumeHelper::consume_normal_api($url, $proxy, $data);

        $source = "from AfwApiConsumeHelper::consume_normal_api($url,$proxy,$data)";
        $return["source"] = $source;

        return $return;
    }

    public static function get_all_dept_from_db() {
        $result = [];

        $rows = AfwDatabase::db_recup_rows("select * from tvtc_hrm.tvtc_dept");

        foreach($rows as $row) {
            $stdobj = new stdClass();
            foreach($row as $col => $value) {
                $stdobj->$col = $value;
            }
            $result[] = $stdobj;
        }

        return ['source'=>'from tvtc_hrm.tvtc_dept', 'result'=>$result, 'success'=>true];
    }

    public static function update_all_organizations($update_in_crm = true, $case = null, $stop_and_debugg_before_update = false, $fromAPI=true)
    {
        if($fromAPI) $res = self::get_all_dept_from_api();
        else $res = self::get_all_dept_from_db();
        

        // AfwAutoLoader::addModule("pag");
        // die("curl on $url => res = ".var_export($res, true));

        $errors_arr = [];
        $infos_arr = [];
        //$log_arr = [];

        if ($res['success']) {
            $result = $res['result'];
            // $update_obj_if_found = true;
            // $create_obj_if_not_found = true;
            $uactive = "N";
            unset($objPbmMatrix);
            $nb_hrm_active_and_good = 0;
            $nb_hrm_disabled = 0;
            $nb_hrm_failed = 0;
            $nb_hrm_updated = 0;
            $nb_hrm_created = 0;
            $nb_hrm_top_of_tree = 0;
            $nb_hrm_no_manager = 0;
            $nb_crm_errors = 0;
            $nb_crm_inserted = 0;
            $nb_crm_updated = 0;
            $nb_crm_ignored = 0;
            $nb_crm_disabled = 0;

            if($fromAPI) AfwDatabase::db_query("truncate tvtc_hrm.tvtc_dept");

            $objPbmMatrix = new HtmlyProcessResultMatrix(2 * count($result));
            foreach ($result as $row) {
                $error_org = "";
                $warning_org = "";
                $success_org = "";

                if($fromAPI) self::create_or_update_tvtc_dept(
                    $row->DEPT_ID,
                    $row->DeptNameArabic,
                    $row->DeptNameEnglish,
                    $row->MAIN_DEPTID,
                    $row->CREATE_DATE,
                    $row->UPDATE_DATE,
                    $row->STD_DPT,
                    $row->STATUS,
                    $row->MGR_EMAIL
                );
                $hrm_code = $row->DEPT_ID;
                $parent_hrm_code = $row->MAIN_DEPTID;
                $titre = $row->DeptNameArabic;
                $titre_short = AfwStringHelper::truncateArabicJomla($titre,92);
                $titre_en = $row->DeptNameEnglish;
                $titre_short_en = AfwStringHelper::truncateEnglishJomla($titre,92);
                if ($case and $case != $hrm_code) continue;

                $status = $row->STATUS;
                if (($status != "A") and ($status != "S") and ($status != "W")) {
                    $error_org .= " > حالة جديدة = [$status] $titre / $titre_en / hrm_code=$hrm_code / parent_hrm_code=$parent_hrm_code";
                } elseif ($status === "A") {
                    $uactive = "Y";
                }

                $id_sh_org = 1;
                $id_sh_parent = 1;
                if ($parent_hrm_code == 1) $parent_hrm_code = "";
                $parentOrg = null;
                if ($parent_hrm_code) {
                    $parentOrg = Orgunit::loadByHRMCode($parent_hrm_code, true, false);
                    if ($parentOrg) $id_sh_parent = $parentOrg->id;
                }

                $id_domain = 4;

                // select STATUS, count(*) from tvtc_dept where (DeptNameArabic like _utf8'وحد%' or DeptNameArabic like _utf8'قسم%') group by STATUS;
                // select STATUS, count(*) from tvtc_dept where (DeptNameArabic like _utf8'%معهد%' or DeptNameArabic like _utf8'%فرع الكلية%') group by STATUS;
                // select STATUS, count(*) from tvtc_dept where (DeptNameArabic like _utf8'%دارة%' or DeptNameArabic like _utf8'%داره%') group by STATUS;
                // select STATUS, count(*) from tvtc_dept where (DeptNameArabic like _utf8'%نائب محافظ%' or DeptNameArabic like _utf8'%محافظ%') group by STATUS;
                

                if (AfwStringHelper::stringStartsWith($titre, "نائب المحافظ"))
                    $id_sh_type = OrgunitType::$ORGUNIT_TYPE_DEPUTY_GOVERNOR;
                // INSTITUTE - معهد  
                elseif (AfwStringHelper::stringStartsWith($titre, "وحدة") 
                    or AfwStringHelper::stringStartsWith($titre, "وحده")
                    or AfwStringHelper::stringStartsWith($titre, "قسم"))
                    $id_sh_type = OrgunitType::$ORGUNIT_TYPE_DIVISION;
                // INSTITUTE - معهد  
                elseif (AfwStringHelper::stringContain($titre, "معهد") or AfwStringHelper::stringContain($titre, "فرع الكلية"))
                {
                    if (AfwStringHelper::stringContain($titre, "صناع")) $id_sh_type = OrgunitType::$ORGUNIT_TYPE_INSTITUTE;
                    else  $id_sh_type = OrgunitType::$ORGUNIT_TYPE_TRAINING_INSTITUTE;
                }
                    
                // إدارة منطقة 
                elseif (AfwStringHelper::stringContain($titre, "الادارة العامة للتدريب التقني و المهني"))
                    $id_sh_type = OrgunitType::$ORGUNIT_TYPE_GENERAL_DEPARTMENT_OF_REGION;
                // إدارة عـــامة 
                elseif ((AfwStringHelper::stringContain($titre, "الادارة") or AfwStringHelper::stringContain($titre, "الإدارة")
                        or AfwStringHelper::stringContain($titre, "الإداره") or AfwStringHelper::stringContain($titre, "الإداره"))
                    and (AfwStringHelper::stringContain($titre, "العامة") or AfwStringHelper::stringContain($titre, "العامه"))
                )
                    $id_sh_type = OrgunitType::$ORGUNIT_TYPE_GENERAL_DEPARTMENT;

                // COLLEGE - كلية  
                elseif (AfwStringHelper::stringContain($titre, "كلية") or AfwStringHelper::stringContain($titre, "كليه"))
                    $id_sh_type = OrgunitType::$ORGUNIT_TYPE_COLLEGE;

                // DEPARTMENT - إدارة  
                else $id_sh_type = OrgunitType::$ORGUNIT_TYPE_DEPARTMENT;

                // The manager
                $id_responsible = null;

                $manager_email = $row->MGR_EMAIL;
                $manager_status = "nothing";
                $force_update_responsible = true;
                $managerObj = null;
                $unit_is_active = ($uactive == "Y");
                if ($unit_is_active) {
                    if ($manager_email) {
                        $managerObj = Employee::loadByEmail($id_sh_org, $manager_email, true);

                        

                        /*
                        if (!$id_responsible and $parentOrg) {
                            $id_responsible = $parentOrg->getManagerId();
                            $force_update_responsible = false;
                        }*/

                        if (!$id_responsible) {
                            $success_org .= "\n > لم يتم العثور على المدير في النظام البريد الالكتروني للمدير = $manager_email"; // => $titre / $titre_en / hrm_code=$hrm_code / parent_hrm_code=$parent_hrm_code row=" . var_export($row, true);
                            $uactive = "N";
                            $manager_status = "manager-not-found";
                            $force_update_responsible = false;
                        }
                    } else {
                        $success_org .= "\n > لا يوجد بريد الكتروني للمدير"; // => $titre / $titre_en / hrm_code=$hrm_code / parent_hrm_code=$parent_hrm_code row=" . var_export($row, true);
                        $uactive = "N";
                        $manager_status = "manager-not-defined";
                        $force_update_responsible = false;
                    }
                } else {
                    $uactive = "N";
                    $manager_status = "unit-disabled";
                    $force_update_responsible = false;
                }

                $the_manager = "manager-" . $id_responsible . "-" . $manager_email;
                if ($manager_status != "unit-disabled") {
                    $objPbmMatrix->addResult(null, $error_org, $warning_org, $success_org, $the_manager, "manager $manager_status", "&clubs;");
                }

                if (!$unit_is_active or $managerObj) {

                    $error_org = "";
                    $info_org = "";
                    $warning_org = "";
                    $manager_status = "";

                    $orgObj = Orgunit::findOrgunit(
                        $id_sh_type,
                        $id_sh_org,
                        $hrm_code,
                        $titre_short,
                        $titre,
                        $titre_short_en,
                        $titre_en,
                        $id_domain,
                        $unit_is_active,
                        true,
                        $hrm_crm = "hrm",
                        $uactive,
                        $id_sh_parent,
                        $id_responsible,
                        $stop_and_debugg_before_update,
                        $row,
                        $force_update_responsible
                    );


                    if ($managerObj) {                            
                        
                        // لأن كل مدير إدارة لا يكون في الموارد البشرية يباشر عمله في نفس هذه الإدارة
                        // بل يكون في الإدارة الأم غالبا
                        // لذلك نضع المدير في الإدارة الأصلية كقسم له لأجل غايات التنظيم في خدمة العملاء
                        $managerObj->set("id_sh_dep", $id_sh_parent);
                        $managerObj->setForce("id_sh_div", $orgObj->id);
                        $managerObj->addRemoveInMfk("jobrole_mfk", [1], []);
                        $managerObj->update();
                        $managerObj->updateMyInfosFromExternalSources('ar', false);
                        $managerObj->updateMyUserInformationAndRoles('ar', '', true, false, false);
                        $id_responsible = $managerObj->id;
                        $manager_status = "found";
                        $force_update_responsible = true;
                    }



                    $action_status_code = "noaction";

                    $the_orgunit = "$titre / $titre_en / o$hrm_code / p$parent_hrm_code";
                    if ($orgObj) {
                        $spc = "&nbsp;";
                        if ($unit_is_active) {
                            $nb_hrm_active_and_good++;
                        }


                        if ($orgObj->error_happened) {
                            $error_org .= " > فشلت عملية تحديث العنصر : $the_orgunit  /active=$uactive => error=" . $orgObj->error_happened;
                        }

                        if (!$unit_is_active) {
                            $nb_hrm_disabled++;
                            $spc = "&empty;";
                        } elseif ($orgObj->is_new) {
                            $nb_hrm_created++;
                            $info_org .= "\n > وحدة جديدة : $the_orgunit";
                            // $warning_org .= "\n > new unit $the_orgunit => id=" . $orgObj->id . "/active=$uactive";
                            $spc = "&starf;";
                        } else {
                            $info_org .= "\n > تحديث الوحدة : $the_orgunit";
                            if ($orgObj->title_changed) {
                                $spc = "&diams;";
                            }
                            $nb_hrm_updated++;
                        }

                        if ($orgObj->action_done) {
                            $info_org .= "\n > " . AfwStringHelper::truncateArabicJomla($orgObj->action_done, 255);
                        }

                        if ($orgObj->title_changed) {
                            $info_org .= "\n > إعادة تسمية " . $orgObj->title_changed;
                            // $warning_org .= "\n > rename unit $the_orgunit => id=" . $orgObj->id . "/active=$uactive ". $orgObj->title_changed;
                        }

                        if ($orgObj->parent_changed) {
                            // $warning_org .= "\n > عنصر الهيكل ($the_orgunit => id=" . $orgObj->id . ") " . $orgObj->parent_changed;
                        }

                        if ($orgObj->log_tech) {
                            // $warning_org .= "\n > " . $orgObj->log_tech;
                        }



                        if ($update_in_crm and $unit_is_active) {
                            if ($parent_hrm_code) {
                                $crmOrg = CrmOrgunit::loadByMainIndex($orgObj->id, true, true);

                                if (!$crmOrg) {
                                    $crm_org_display = $orgObj->getDisplay("ar");
                                    $action_status_code = "failed";
                                    $action_status = "فشل";
                                    $action = "إنشاء/تحديث";
                                    $nb_crm_errors++;
                                    $orgObj->alert .= " crmcreatefailed";
                                } else {
                                    $crmOrg->is_new ? $nb_crm_inserted++ : $nb_crm_updated++;
                                    $crmOrg->is_new ? $spc = "&hearts;" : $spc = "&diams;";
                                    $crmOrg->is_new ? $action_type_alert = "crmcreated" : $action_type_alert = "crmupdated";
                                    $orgObj->alert .= " " . $action_type_alert;
                                    $crm_org_display = $crmOrg->getDisplay("ar") . " : " . $crmOrg->id;
                                    $action_status_code = "succeeded";
                                    $action_status = "تم";
                                    $action = $crmOrg->is_new ? "إنشاء" : "تحديث";
                                    // add the manager as crm employee if exists
                                    if($id_responsible and ($id_responsible != $orgObj->old_id_responsible)) {
                                        $crmEmplManager = CrmEmployee::loadByMainIndex($orgObj->id, $id_responsible, true, 'Y', 'Y', 'N', 'N', 'N');
                                        if($orgObj->old_id_responsible) {
                                            $oldCrmEmplManager = CrmEmployee::loadByMainIndex($orgObj->id, $orgObj->old_id_responsible);
                                            if($oldCrmEmplManager) $oldCrmEmplManager->hide();
                                        }
                                        
                                    }
                                }

                                $info_org .= "\n > خدمة العملاء : $action_status $action العنصر : $crm_org_display ";
                            } else {
                                // orgunits how dont have parent are in TOP and should not be in CRM
                                $nb_crm_ignored++;
                            }
                        }
                        // @note : if we found by name no disable because sometime we disable old unit and create new one 
                        // with same name just parent change
                        // so we disable only if found by code
                        elseif ($update_in_crm and (!$unit_is_active) and $orgObj->foundByCode) {
                            $crmOrg = CrmOrgunit::loadByMainIndex($orgObj->id, false, false);
                            if ($crmOrg) {
                                $action_status_code = "crm-unit-hidden";
                                $crm_org_display = $crmOrg->getDisplay("ar") . " : " . $crmOrg->id;
                                $crmOrg->hide();
                                $nb_crm_disabled++;
                                $orgObj->alert .= " crmdisabled";
                                // $log_arr[] = "$the_orgunit has been disabled in CRM";
                                $info_org .= "\n > خدمة العملاء : تم تعطيل العنصر : $crm_org_display !!!!";
                            } else {
                                $action_status_code = "crm-unit-to-hide-not-found";
                            }
                        }

                        if ($error_org) $errors_arr[] = $error_org;
                        if ($info_org) $infos_arr[] = $info_org;
                        // if($log_org) $log_arr[] = $log_org;

                        $log = "";
                        $log .= $error_org ? "خطأ : $error_org \n" : "";
                        $log .= $info_org ? "اجراء : $info_org \n" : "";
                        $log .= $warning_org ? "تنبيه : $warning_org \n" : "";

                        $orgObj->set("log", $log);
                        $orgObj->update();

                        $css_added = $orgObj->alert;
                    } else {
                        $spc = "!";
                        $errors_arr[] = $error_org =  "فشل انشاء أو العثور على العنصر : $titre / $titre_en / c$hrm_code / p$parent_hrm_code /active=$uactive";
                        $css_added = "error";
                        $manager_status = "";
                        $action_status_code = "failed";
                        $nb_crm_errors++;
                        $nb_hrm_failed++;
                    }

                    $objPbmMatrix->addResult(null, $error_org, $warning_org, $info_org, $the_orgunit, "orgunit $action_status_code " . $css_added, $spc);
                } /*elseif (!$parentOrg) {
                    $nb_hrm_top_of_tree++;
                }*/ elseif (!$managerObj) {
                    $nb_hrm_no_manager++;
                }
            }

            $html_recap = "<div class='html-recap'>";

            $html_recap .= "<span><p>Errors :</p> $nb_crm_errors</span>";
            $html_recap .= "<span><p>Inserted :</p> $nb_crm_inserted</span>";
            $html_recap .= "<span><p>Updated :</p> $nb_crm_updated</span>";
            $html_recap .= "<span><p>Ignored :</p> $nb_crm_ignored</span>";
            $html_recap .= "<span><p>Disabled :</p> $nb_crm_disabled</span>";

            $html_recap .= "<span><p>Hrm-Failed :</p> $nb_hrm_failed</span>";
            $html_recap .= "<span><p>Hrm-Disabled :</p> $nb_hrm_disabled</span>";
            $html_recap .= "<span><p>Hrm-No-Manager :</p> $nb_hrm_no_manager</span>";
            $html_recap .= "<span><p>Hrm-Top-Tree :</p> $nb_hrm_top_of_tree</span>";
            $html_recap .= "<span><p>Hrm-Good :</p> $nb_hrm_active_and_good</span>";
            $html_recap .= "<span><p>Hrm-Inserted :</p> $nb_hrm_created</span>";
            $html_recap .= "<span><p>Hrm-Updated :</p> $nb_hrm_updated</span>";

            $html_recap .= "</div>";
            $html_recap .= "<div class='html-legend'>";
            $html_recap .= "<div class=\"htmly-matrix htmly-cell-legend\">";
            $html_recap .= "   <div class=\"htmly-cell htmly-matrix-success orgunit succeeded  crmcreated\"><span>♥</span></div>";
            $html_recap .= "   <div class=\"htmly-cell-title\">وحدة جديدة</div>";
            $html_recap .= "</div>";
            $html_recap .= "<div class=\"htmly-matrix htmly-cell-legend\">";
            $html_recap .= "   <div class=\"htmly-cell htmly-matrix-success orgunit succeeded  crmupdated\"><span>&diams;</span></div>";
            $html_recap .= "   <div class=\"htmly-cell-title\">وحدة تم تحديثها</div>";
            $html_recap .= "</div>";
            $html_recap .= "<div class=\"htmly-matrix htmly-cell-legend\">";
            $html_recap .= "   <div class=\"htmly-cell htmly-matrix-success manager manager-not-defined\"><span>♣</span></div>";
            $html_recap .= "   <div class=\"htmly-cell-title\">وحدة ليس لها مدير يتم تجاهلها</div>";
            $html_recap .= "</div>";
            $html_recap .= "<div class=\"htmly-matrix htmly-cell-legend\">";
            $html_recap .= "   <div class=\"htmly-cell htmly-matrix-success orgunit loaded titlechanged\"><span>&diams;</span></div>";
            $html_recap .= "   <div class=\"htmly-cell-title\">وحدة تم تعديل مسماها</div>";
            $html_recap .= "</div>";
            $html_recap .= "<div class=\"htmly-matrix htmly-cell-legend\">";
            $html_recap .= "   <div class=\"htmly-cell htmly-matrix-success orgunit loaded unit-disabled\"><span>&empty;</span></div>";
            $html_recap .= "   <div class=\"htmly-cell-title\">وحدة تم تعطيلها</div>";
            $html_recap .= "</div>";

            $html_recap .= "</div>";

            $objPbmMatrix->addRecap($html_recap);
            $objPbmMatrix->addDetailViewer();

            $matrix_html = $objPbmMatrix->renderHtml();
        } else {
            $source = $res['source'];
            $matrix_html = "";
            $errors_arr[] = "loading organizations data $source failed ! result is : " . var_export($res, true);
        }



        // $res['log'] = $log_arr;
        $res['errors'] = $errors_arr;
        $res['infos'] = $infos_arr;
        $res['matrix'] = $matrix_html;

        return $res;
    }

    /**
     * @param Orgunit $orgunit
     */
    public static function trigger_new_organization($orgunit)
    {

        // to be implemented if needed in the future
    }

    /**
     * @param Orgunit $orgunit
     * @param array $fields_updated
     */
    public static function trigger_update_organization($orgunit, $fields_updated)
    {
        $crm_module_exists = AfwAutoLoader::moduleExists("crm");
        if ($crm_module_exists) {
            $crmOrg = CrmOrgunit::loadByMainIndex($orgunit->id, false, false);
            if (
                $orgunit and
                $fields_updated["id_responsible"] and
                $orgunit->getVal("id_responsible") and
                !$crmOrg->getVal("id_responsible")
            ) {
                $crmOrg->set("id_responsible", $orgunit->getVal("id_responsible"));
            }
        }
    }



    public static function trigger_before_delete_organization($id, $id_replace = 0, $simul = false)
    {
        $server_db_prefix = AfwSession::config('db_prefix', 'default_db_');

        if ($id_replace == 0) {
            $obj = new CrmOrgunit();
            $obj->where("orgunit_id = '$id' and active='Y'");
            $nbRecords = $obj->count();
            // check if there's no record that block the delete operation
            if ($nbRecords > 0) {
                return [false, "Used in Crm Orgunit"];
            }
            // if there's no record that block the delete operation perform the delete of the other records linked with me and deletable
            if (! $simul) {
                $obj->deleteWhere("orgunit_id = '$id'");
            }
            unset($obj);
            // crm.request-الجهة المعنية بالطلب	concerned_orgunit_id  أنا تفاصيل لها-OneToMany
            $obj = new Request();
            $obj->where("concerned_orgunit_id = '$id' and active='Y' ");
            $nbRecords = $obj->count();
            // check if there's no record that block the delete operation
            if ($nbRecords > 0) {
                return [false, "Used in some Media requests(s) as Customer orgunit"];
            }
            // if there's no record that block the delete operation perform the delete of the other records linked with me and deletable
            if (!$simul) {
                AfwDatabase::db_query("delete from ${server_db_prefix}crm.request where concerned_orgunit_id = '$id' and active='N'");
            }
            unset($obj);
            // crm.request-الإدارة المكلفة بالإجابة	orgunit_id  أنا تفاصيل لها-OneToMany
            $obj = new Request();
            $obj->where("orgunit_id = '$id' and active='Y' ");
            $nbRecords = $obj->count();
            // check if there's no record that block the delete operation
            if ($nbRecords > 0) {
                return [false, "Used in some Media requests(s) as B m orgunit"];
            }
            // if there's no record that block the delete operation perform the delete of the other records linked with me and deletable
            if (!$simul) {
                AfwDatabase::db_query("delete from ${server_db_prefix}crm.request where orgunit_id = '$id' and active='N'");
            }

            // crm.response-الجهة المكلفة بالرد	orgunit_id  أنا تفاصيل لها-OneToMany

            $obj = new Response();
            $obj->where("orgunit_id = '$id' and active='Y' ");
            $nbRecords = $obj->count();
            // check if there's no record that block the delete operation
            if ($nbRecords > 0) {
                return [false, "Used in some Media responses(s) as Orgunit"];
            }
            // if there's no record that block the delete operation perform the delete of the other records linked with me and deletable
            if (! $simul) {
                AfwDatabase::db_query("delete from ${server_db_prefix}crm.response where orgunit_id = '$id' and active='N'");
            }

            // crm.crm_orgunit-الجهة المكلفة بالرد	orgunit_id  جزء مني ولا يعمل إلا بي-OneToOneBidirectional

            $obj = new CrmOrgunit();
            $obj->where("orgunit_id = '$id' and active='Y' ");
            $nbRecords = $obj->count();
            // check if there's no record that block the delete operation
            if ($nbRecords > 0) {
                return [false, "Used in some Crm orgunits(s) as Orgunit"];
            }
            // if there's no record that block the delete operation perform the delete of the other records linked with me and deletable
            if (!$simul) {
                AfwDatabase::db_query("delete from ${server_db_prefix}crm.crm_orgunit where orgunit_id = '$id' and active='N'");
            }

            $obj = new CrmOrgunit();
            $obj->where("orgunit_id = '$id' and active='Y' ");
            $nbRecords = $obj->count();
            // check if there's no record that block the delete operation
            if ($nbRecords > 0) {
                return [false, "Used in some Crm investigators as the organization unit"];
            }
            // if there's no record that block the delete operation perform the delete of the other records linked with me and deletable
            if (! $simul) {
                AfwDatabase::db_query("delete from ${server_db_prefix}crm.crm_orgunit where orgunit_id = '$id' and active='N'");
            }
        } else {
            if (! $simul) {
                CrmOrgunit::replaceOrgunitBy($id, $id_replace);
            }
        }

        return [true, ''];
    }

    public static function trigger_before_delete_employee($id, $id_replace = 0, $simul = false)
    {
        return [true, ''];
    }
}
