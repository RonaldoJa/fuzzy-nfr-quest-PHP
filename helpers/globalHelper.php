<?php


class GlobalHelper
{
    static $expectedFields = [
        "nfr",
        "variable",
        "feedback1",
        "value",
        "feedback2",
        "recomend",
        "other_recommended_values",
        "feedback3",
        "validar"
    ];

    public static function generalResponse($data = null, $message = '', $status = 200)
    {
        $response = [
            'data' => $data,
            'message' => $message
        ];

        http_response_code($status);
        echo json_encode($response);
    }

    public static function generateRandomString($length) {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, $length);
    }

    public static function isValidDate($date) {
        $format = 'Y-m-d H:i:s';
        $dateObj = DateTime::createFromFormat($format, $date);
        
        if ($dateObj && $dateObj->format($format) === $date) {
            return true;
        }

        return false;
    }

    public static function validateArrayFields($array) {
        foreach ($array as $element) {
            foreach (self::$expectedFields as $field) {
                if (!isset($element[$field]) || trim($element[$field]) === '') {
                    return false;
                }
            }
        }
        return true;
    }
}
