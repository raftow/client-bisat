<?php

class CustomerExtendedInfo
{

    public static function getExtendedInfo($customerObj)
    {

        $assass2FieldNames = [
            "studentUniqueId" => "الرقم الفريد",
            "institute" => "المؤسسة التعليمية",
            "arabicFirstName" => "الاسم الأول عربي",
            "arabicSecondName" => "الاسم الثاني عربي",
            "arabicThirdName" => "الاسم الثالث عربي",
            "arabicFourthName" => "الاسم الرابع عربي",
            "englishFirstName" => "الاسم الأول انجليزي",
            "englishSecondName" => "الاسم الثاني انجليزي",
            "englishThirdName" => "الاسم الثالث انجليزي",
            "englishFourthName" => "الاسم الرابع انجليزي",
            "identityType" => "نوع الهوية",
            "identityNumber" => "الهوية",
            "birthDate" => "تاريخ الميلاد",
            "gender" => "الجنس",
            "nationality" => "الجنسية",
            "isSpecialNeeds" => "من ذوي الاحتياجات الخاصة؟",
            "specialNeedsType" => "نوع الاحتياجات الخاصة",
            "email" => "البريد الالكتروني",
            "mobileNumber" => "رقم الجوال",
            "lastUpdateDate" => "تاريخ تحديث السجل في المؤسسة التعليمية",
            "hasScholarship" => "هل للطالب منحة؟",
            "scholarshipType" => "نوع المنحة الدراسية",
            "scholarshipClassification" => "تصنيف المنحة",
            "studentAcademicNumber" => "رقم الطالب الأكاديمي",
            "scientificDegree" => "الدرجة العلمية",
            "academicStatus" => "الحالة الاكاديمية (الوضع العام)",
            "studyLocation" => "المدينة (مقر الدراسة الجغرافي)",
            "currentCollege" => "الكلية الحالية",
            "acceptedCollege" => "الكلية المقبول فيها",
            "section" => "القسم",
            "major" => "التخصص العام (الرئيس)",
            "minor" => "التخصص الدقيق (الفرعي)",
            "specialtyClassification" => "تصنيف التخصص",
            "educationalSubLevel" => "المستوى التعليمي الفرعي",
            "includedSpecialization" => "التخصص المندرج",
            "studyProgramPeriod" => "مدة برنامج الدراسة",
            "studyProgramPeriodUnit" => "وحدة قياس مدة البرنامج",
            "requestedCreditHoursCount" => "عدد الساعات الدراسية المطلوبة",
            "registeredCreditHoursCount" => "عدد الساعات الدراسية المسجلة",
            "passedCreditHoursCount" => "عدد الساعات الدراسية المجتازة",
            "remainingCreditHoursCount" => "عدد الساعات الدراسية المتبقية",
            "registrationStatus" => "حالة التسجيل",
            "currentAcademicYearDate" => "تاريخ السنة الدراسية الحالية ميلادي",
            "currentSemester" => "الفصل الدراسي",
            "graduationDate" => "تاريخ التخرج",
            "studyType" => "نوع الدراسة",
            "admissionDate" => "تاريخ الالتحاق ميلادي",
            "hasStudentReward" => "هل للطالب مكافئة؟",
            "studentRewardAmount" => "المكافئة الطلابية",
            "gpaType" => "نوع المعدل التراكمي",
            "gpa" => "المعدل التراكمي",
            "rating" => "تقدير الطالب",
            "hasThesis" => "هل يوجد رسالة؟",
            "thesisTitle" => "عنوان الرسالة",
            "lastAcademicStatusUpdateDate" => "تاريخ اخر تحديث للحالة الاكاديمية",
            "disclaimerDate" => "تاريخ اخلاء الطرف",
            "isLastAcademicDataRecord" => "اخر سجل للبيانات الأكاديمية؟"
        ];

        $arrExtendedInfo = [];
        $res = [];
        // if trainee - / متدرب  
        if (($customerObj->getVal("customer_type_id") == 6) or
            ($customerObj->getVal("ref_num") and in_array($customerObj->getVal("customer_type_id"), [3, 7, 8]))
        ) {
            // look data from assass system api
            $assass_arr = AfwSession::config("assass", "");

            $assass_api = $assass_arr['api-query'];

            $refNum = $customerObj->getVal("ref_num");
            if ($customerObj->getVal("customer_type_id") == 6) $refNum = $customerObj->getVal("idn");

            $assassLetter = "";
            if ($customerObj->getVal("customer_type_id") == 6) $assassLetter = "A";
            if ($customerObj->getVal("customer_type_id") == 7) $assassLetter = "B";
            if ($customerObj->getVal("customer_type_id") == 8) $assassLetter = "C";
            $studentUniqueId = $assassLetter . $refNum;

            $arrExtendedInfo[$studentUniqueId] = [];

            $data = ['id' => $studentUniqueId];



            try {
                $res['log'] = "consuming assass api : " . $assass_api . " with data : " . var_export($data, true);
                $res = AfwApiConsumeHelper::consume_normal_api($assass_api, "hq-proxy|8080", $data, false, false, true, true);



                if ($res['success']) {
                    $result = $res['result'];
                    if (!$result['message']) $result['status'] = true;
                    if ($result['status']) {
                        $arrExtendedInfo[$studentUniqueId] = $result;
                    } else {
                        $res['success'] = false;
                        $res['message'] = $result['message'];
                    }
                } else {
                    $res['success'] = false;
                    $res['message'] = "please check with the system administrator";
                }
            } catch (Exception $e) {
                $res['success'] = false;
                $res['message'] = "please check with the system administrator";
                $res['error'] = "Error while consuming assass api for studentUniqueId $studentUniqueId : " . $e->getMessage();
            }
        } else {
            $res['success'] = false;
            $res['message'] =  $res['error'] = "For this type of customer no implementation exists to load extended info";
        }


        return [$arrExtendedInfo, $assass2FieldNames, $res];
    }


    public static function getExtendedInfoHtml($customerObj, $level="admin", $lang = "ar")
    {
        $objme = AfwSession::getUserConnected();
        $arrExtendedInfo = [];
        $assass2FieldNames = [];
        $confidential_class = '';
        $customerObj_id = $customerObj->id;
        list($arrExtendedInfo, $assass2FieldNames, $res) = CustomerExtendedInfo::getExtendedInfo($customerObj);
        $html = "";
        if ($res['success']) {
            $html = "<div id='div-request-additional-$customerObj_id level-$level' class='data-report security $confidential_class'>";
            foreach ($arrExtendedInfo as $studentUniqueId => $extendedInfoArr) {

                $api_mobile = AfwFormatHelper::formatMobile($extendedInfoArr["student"]["mobileNumber"]);

                if (($extendedInfoArr["student"]["identityNumber"] != $customerObj->getVal("idn")) or
                    ($api_mobile != $customerObj->getVal("mobile") and
                        $extendedInfoArr["student"]["email"] != $customerObj->getVal("email"))
                ) {
                    $return = "<div class='data-report security'>" . $customerObj->tm("customer advanced-information is confidential, please use an account with the same mobile number and email you used to register with your organization") . "</div>";
                    if ($objme and $objme->isSuperAdmin()) {
                        $return .= "res = " . var_export($res, true);
                        $return .= "<br>\nlog : " . var_export($extendedInfoArr["student"], true) . " != " . var_export(["idn" => $customerObj->getVal("idn"), "mobile" => $customerObj->getVal("mobile"), "email" => $customerObj->getVal("email")], true);
                    }

                    return $return;
                }

                // $html .= var_export($extendedInfoArr["details"], true)."<- details <br>\n";
                // Process each extended info entry
                foreach ($extendedInfoArr["student"] as $fieldName => $fieldValue) 
                {
                    $html_prefix = "";
                    $prefix = "";
                    $hide = false;
                    if (AfwStringHelper::stringEndsWith($fieldName, "Code")) {
                            $prefix = "رمز";
                            $html_prefix = "<hr class='hrthin'>";
                            $hide = ($level != "admin");
                    }
                    if (AfwStringHelper::stringEndsWith($fieldName, "Name")) 
                    {
                        $subField = substr($fieldName, 0, -4);
                        if($assass2FieldNames[$subField]) $prefix = "مسمى";
                    }


                    if ($prefix) {
                        $subField = substr($fieldName, 0, -4);
                        $fieldLabel = $assass2FieldNames[$subField] ?? ($assass2FieldNames[$fieldName] ?? $fieldName);
                    } else {
                        $fieldLabel = $assass2FieldNames[$fieldName] ?? $fieldName;
                    }

                    if ((AfwStringHelper::stringStartsWith($fieldName, "has")) or (AfwStringHelper::stringStartsWith($fieldName, "is"))) 
                    {
                        $fieldValue = ($fieldValue == 1) ? "نعم" : "لا";
                    }

                    if(!$hide)
                    {
                        if($level != "admin") $prefix = "";
                        $html .= $html_prefix . "<div class='report-item report-label'>$prefix $fieldLabel <!-- $fieldName --></div><div class='report-item report-value'>$fieldValue</div>";
                    }
                }

                $html .= "<hr><br>";
                foreach ($extendedInfoArr["details"] as $detailsRow) {
                    foreach ($detailsRow as $fieldName => $fieldValue) {
                        $html_prefix = "";
                        $prefix = "";
                        $hide = false;

                        if (AfwStringHelper::stringEndsWith($fieldName, "Code")) {
                            $prefix = "رمز";
                            $html_prefix = "<hr class='hrthin'>";
                            $hide = ($level != "admin");
                        }
                        if (AfwStringHelper::stringEndsWith($fieldName, "Name")) $prefix = "مسمى";

                        if ($prefix) {
                            $subField = substr($fieldName, 0, -4);
                            $fieldLabel = $assass2FieldNames[$subField] ?? ($assass2FieldNames[$fieldName] ?? $fieldName);
                        } else {
                            $fieldLabel = $assass2FieldNames[$fieldName] ?? $fieldName;
                        }

                        if ((AfwStringHelper::stringStartsWith($fieldName, "has")) or (AfwStringHelper::stringStartsWith($fieldName, "is")))  
                        {
                            $fieldValue = ($fieldValue == 1) ? "نعم" : "لا";
                        }

                        if(!$hide)
                        {
                            if($level != "admin") $prefix = "";
                            $html .= $html_prefix . "<div class='report-item report-label'>$prefix $fieldLabel <!-- $fieldName --></div><div class='report-item report-value'>$fieldValue</div>";
                        }
                        
                    }
                    $html .= "<hr><br>";
                }
            }

            $html .= "</div>";
            $confidential_button = "";
            $html .= $confidential_button;
        }



        return array($html, $res);
    }
}
