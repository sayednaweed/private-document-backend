<?php

if (!function_exists('encodeBinaryFields')) {
    // /**
    //  * Example helper function
    //  *
    //  * @param string $string
    //  * @return string
    //  */
    // function custom_helper($string)
    // {
    //     return strtoupper($string);
    // }
    function encodeBinaryFields($data)
    {
        // Loop through the data and encode any binary fields to base64
        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) > 0) {
                // Check if the field value is binary data by detecting any non-printable characters
                if (preg_match('/[^\x20-\x7E]/', $value)) {
                    // If it's binary data, encode it to base64
                    $data[$key] = base64_encode($value);
                }
            }
        }
        return $data;
    }
}
