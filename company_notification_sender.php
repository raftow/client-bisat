<?php
class CompanyNotificationSender
{

    /**
     * @param string $mobile
     * @param string $message
     */
    public static function sendSMS($mobile, $message, $encoding = "utf-8")
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if (AfwSession::config("sms_simulation_mode", false)) {
            AfwSession::pushInformation("SMS simuation to $mobile :<br>" . $message);
            $sms_ok = false;
            $sms_info = "only simulated with push information";
            return array($sms_ok, $sms_info);
        } else {
            $info = "";

            $mobile = AfwFormatHelper::formatMobile($mobile);

            $mobile_error = AfwFormatHelper::mobileError($mobile, $lang);

            $body = $message;
            $error_details_if_failed = " failed to send to mobile=$mobile the body=$body";

            if ($mobile_error) return array(false, "mobile format error : " . $mobile_error . $error_details_if_failed);

            $res = self::hzmSMS($mobile, $body, $encoding);
            $the_username = $res->SmsUser;
            $the_username_sent = $res->user_name_sent;
            $error_details_if_failed .= " result of hzmSMS : " . var_export($res, true);

            if ($res->SendSMSResult == 'TRUE') {
                $info = "SMS sent successfully to $mobile with reponsible user name [$the_username / $the_username_sent] " . date("Y-m-d H:i:s");

                return array(true, $info);
            } elseif ($res->SendSMSResult == 'الرسالة مرسلة من قبل') {
                return array(false, $res->SendSMSResult . $error_details_if_failed);
            } else {
                $message = "failed to send SMS to $mobile with reponsible user name [$the_username / $the_username_sent] " . date("Y-m-d H:i:s");
                if ($mobile == "0598988330") {
                    $message .= " \n sms server api response : [" . $res->SendSMSResult . "]";
                    $message .= " \n [res=" . var_export($res, true) . "]";
                    $message .= " \n error=" . $error_details_if_failed;
                }

                return array(false, $message);
            }
        }
    }

    /**
     * @param string $mobile_number
     * @param string $message
     * @param string $encoding
     * @return object|false
     */
    public static function hzmSMS($mobile_number, $message, $encoding = "utf-8")
    {
        // throw new AfwRuntimeException("hzmSMS what params", ['ALL'=>true], ['mobile_number'=>$mobile_number, 'message'=>$message, 'encoding'=>$encoding]);
        // global $smsSender_wsdlUrl;
        $file_dir_hzm = dirname(__FILE__);
        $sms_config_config_file = "$file_dir_hzm/../config/sms_config.php";
        if (!file_exists($sms_config_config_file)) {
            throw new AfwRuntimeException("sms config file " . $sms_config_config_file . " not found !");
        }
        $arrConfig = include($sms_config_config_file);
        if ((!$arrConfig) or (!is_array($arrConfig))) {
            throw new AfwRuntimeException("sms config file should return the config array, but it return : " . var_export($arrConfig, true));
        }

        if (!$arrConfig["type"]) $arrConfig["type"] = "soap";
        if ($arrConfig["type"] == "soap") {
            // die("rafik crm debugg 250908 arrConfig=".var_export($arrConfig, true));
            $return = self::soapSMS($mobile_number, $message, $arrConfig, $encoding);
            if (is_object($return) and ($mobile_number == "0598988330")) {
                $return->mobile_number = $mobile_number;
                $return->message = $message;
                $return->arrConfig = $arrConfig;
                $return->encoding = $encoding;
            }

            return $return;
        }

        throw new AfwRuntimeException("sms api type " . $arrConfig["type"] . " not implemented !");
    }


    /**
     * @param string $mobile_number
     * @param string $message
     * @param array $arrConfig
     * @param string $encoding
     * @return object|false
     */
    public static function soapSMS($mobile_number, $message, $arrConfig, $encoding = "utf-8")
    {
        // 
        // throw new AfwRuntimeException("soapSMS what params", ['ALL'=>true], ['mobile_number'=>$mobile_number, 'message'=>$message, 'arrConfig'=>$arrConfig, 'encoding'=>$encoding]);


        foreach ($arrConfig as $key => $val) $$key = $val;
        $hard_params = $arrConfig["hard-params"];
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                )
            );
            $context = stream_context_create($opts);


            $soapClientOptions = array(
                'stream_context' => $context,
                'cache_wsdl' => 'WSDL_CACHE_NONE',
                'encoding' => $encoding
            );

            $NumberCol = $params['MOBILE-NUMBER-PARAM'];
            if (!$NumberCol) $NumberCol = "Number";

            $MessageCol = $params['MESSAGE-BODY-PARAM'];
            if (!$MessageCol) $MessageCol = "Message";

            // die("new SoapClient(smsSender_wsdlUrl = $smsSender_wsdlUrl, soapClientOptions = ".var_export($soapClientOptions, true).")");
            $soapClient = new SoapClient($smsSender_wsdlUrl, $soapClientOptions);  // ' UTF-8'
            // die("new SoapClient(smsSender_wsdlUrl = $smsSender_wsdlUrl, soapClientOptions = ".var_export($soapClientOptions, true).") => ".var_export($soapClient, true));
            $error = 0;


            //$info = $soapClient->__call($method, array($params));

            $soapParams = array($NumberCol => $mobile_number, $MessageCol => $message, 'APPLICATION_ID' => $application_id, 'PROCESS_ID' => $process_id, 'USER_NAME' => $user_name,);
            foreach ($hard_params as $key => $val) $soapParams[$key] = $val;
            // if($mobile_number=="0598988330") die("with hard_params=".var_export($hard_params, true)." arrConfig=".var_export($arrConfig, true)." soapClient->$method(soapParams = ".var_export($soapParams, true).") will be executed ");
            $info = $soapClient->$method($soapParams);
            // if($mobile_number=="0598988330") die("soapClient->$method(soapParams = ".var_export($soapParams, true).") => ".var_export($info, true));
        } catch (SoapFault $fault) {
            $message = "hzm SMS sender failed with fault : " . var_export($fault, true);
            AfwSession::log($message);
            throw $fault;
            $error = 1;
        } catch (Exception $e) {
            throw $e;
            $message = "hzm SMS sender failed with exception : " . var_export($e, true);
            AfwSession::log($message);
            $error = 1;
        }

        unset($soapClient);

        if ($error == 0) {
            if (is_object($info)) {
                if ($mobile_number == "0598988330") $info->Params = $soapParams;
                $info->user_name_sent = $user_name;
            }
            return $info;
        } else {
            return false;
        }
    }


    /**
     * @param array $payload  ex ["email"=>"", "mobile"=>"", "body"=>"", "subject"=>""]
     */
    public static function sendNotification($payload, $encoding = "utf-8")
    {
        $response = ["success" => true,
            "message" => "message_sent",
            "data" => null];

        return [
                "status"   => 1,
                "response" => $response,
        ];

        $base_url = AfwSession::config("api_base_url", "yyyyyyyy");
        $token    = AfwSession::config("api_token", "XXXXYYY");

        $ch = curl_init("$base_url/message/send");
        curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                        "Authorization: Bearer $token",
                        "Content-Type: application/json",
                        "Accept: application/json",
                ],
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
                "status"   => $status,
                "response" => json_decode($response, true),
        ];
    }
}
