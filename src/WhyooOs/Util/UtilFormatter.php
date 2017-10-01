<?php


namespace WhyooOs\Util;


class Utilformatter
{

    /**
     * 10/2017
     * TODO: https://github.com/danielstjules/Stringy
     *
     * @param $array
     * @return string
     */
    public static function formatDictText($array)
    {
        $maxLengthKey = 0;
        $maxLengthVal = 0;
        foreach ($array as $key => $val) {
            $maxLengthKey = max(mb_strlen($key), $maxLengthKey);
            $maxLengthVal = max(mb_strlen($val), $maxLengthVal);
        }


        $out = "+-" . str_pad('', $maxLengthKey, "-") . "-+-" . str_pad('', $maxLengthVal, "-") . "-+\n";
        foreach ($array as $key => $val) {
            $out .= "| " . str_pad($key, $maxLengthKey) . " | " . str_pad($val, $maxLengthVal) . " |\n";
        }
        $out .= "+-" . str_pad('', $maxLengthKey, "-") . "-+-" . str_pad('', $maxLengthVal, "-") . "-+\n";

        return $out;
    }


    /**
     * 10/2017
     *
     * @param $array
     * @param $keys
     * @return string
     */
    public static function formatDictHtml($array, $keys)
    {
        $translations = [ // FIXME: should be parameter
            'firstname' => "Vorname",
            'lastname' => "Nachnahme",
            'email' => 'Email-Adresse',
            'is_subscribed' => "Newsletter",
            'yourmsg' => "Nachricht",
            'country_id' => "Land",
            'company' => "Firma",
        ];


        $out = "<table>\n";
        foreach ($keys as $key) {
            $val = htmlentities($array[$key]);
            if (array_key_exists($key, $translations)) {
                $key = $translations[$key];
            } else {
                $key = ucfirst($key);
            }
            $out .= "<tr><td>$key&nbsp;&nbsp;&nbsp;&nbsp;</td><td>$val</td></tr>\n";
        }
        $out .= "</table>\n";

        return $out;
    }

}