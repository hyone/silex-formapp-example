<?php

namespace Hyone\FormExample;

class Util
{
    // data processing when success
    // example: append to CSV
    public static function putData($app, $data, $file) {
        $fh = fopen($file, 'a');
        if ($fh === false) {
            throw new \Exception("Can't open file: " . $file);
        }
        if (flock($fh, LOCK_EX)) {
            fputcsv($fh, $data);
            flock($fh, LOCK_UN);
            fclose($fh);
        } else {
            fclose($fh);
            throw new \Exception("Can't lock csv file: " . $file);
        }
    }
}
