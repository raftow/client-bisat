<?php
class BisatCrmUtils
{
    public static function idn_to_idn_type(string $idn)
    {
        if (is_integer($idn) and ($idn > 10000000) and ($idn < 20000000)) {
            return 5;
        }

        list($idn_correct, $idn_type) = AfwFormatHelper::getIdnTypeId($idn);

        return $idn_type;
    }

    public static function additional_idn_types()
    {
        $list_of_items = array();
        $list_of_items[4] = "جواز سفر";
        $list_of_items[5] = "رقم عميل";
        return  $list_of_items;
    }
}
