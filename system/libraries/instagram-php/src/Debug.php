<?php

namespace InstagramAPI;

class Debug
{
    /*
     * If set to true, the debug logs will, in addition to being printed to console, be placed in the file noted below in $debugLogFile
     */
    public static $debugLog = false;
    /*
     * The file to place debug logs into when $debugLog is true
     */
    public static $debugLogFile = 'debug.log';

   /*
    * The path to place debug logs into when $debugLog is true and file storage is not used.
    */
    public static $debugLogPath = null;

    public static function printRequest(
        $method,
        $endpoint,
        $path = null,
        $cliDebug = false)
    {
        if (PHP_SAPI === 'cli') {
            $cMethod = Utils::colouredString("{$method}:  ", 'light_blue');
        } else {
            $cMethod = $method.':  ';
        }
        if ($cliDebug) {
            echo $cMethod.$endpoint."\n";
        }
        if (self::$debugLog && ($path !== null)) {
            file_put_contents($path.'/'.self::$debugLogFile, date('Y-m-d H:i:s').'  '.$method.':  '.$endpoint."\n", FILE_APPEND | LOCK_EX);
        }
    }

    public static function printUpload(
        $uploadBytes,
        $path = null,
        $cliDebug = false)
    {
        if (PHP_SAPI === 'cli') {
            $dat = Utils::colouredString('→ '.$uploadBytes, 'yellow');
        } else {
            $dat = '→ '.$uploadBytes;
        }
        if ($cliDebug) {
            echo $dat."\n";
        }
        if (self::$debugLog && ($path !== null)) {
            file_put_contents($path.'/'.self::$debugLogFile, "→  $uploadBytes\n", FILE_APPEND | LOCK_EX);
        }
    }

    public static function printHttpCode(
        $httpCode,
        $bytes,
        $path = null,
        $cliDebug = false)
    {
        if (PHP_SAPI === 'cli') {
            if ($cliDebug) {
                echo Utils::colouredString("← {$httpCode} \t {$bytes}", 'green')."\n";
            }
        } else {
            if ($cliDebug) {
                echo "← {$httpCode} \t {$bytes}\n";
            }
        }
        if (self::$debugLog && ($path !== null)) {
            file_put_contents($path.'/'.self::$debugLogFile, "← {$httpCode} \t {$bytes}\n", FILE_APPEND | LOCK_EX);
        }
    }

    public static function printResponse(
        $response,
        $truncated = false,
        $path = null,
        $cliDebug = false)
    {
        if (PHP_SAPI === 'cli') {
            $res = Utils::colouredString('RESPONSE: ', 'cyan');
        } else {
            $res = 'RESPONSE: ';
        }

        preg_match_all('/window._sharedData = (.*);<\/script>/m', $response, $matches, PREG_SET_ORDER, 0);

        if (!empty($matches)) {
            if (count($matches[0]) > 1) {
                $response = $matches[0][1];
            }
        }

        if ($truncated && mb_strlen($response, 'utf8') > 1000) {
            $response = mb_substr($response, 0, 1000, 'utf8').'...';
        }
        if ($cliDebug) {
            echo $res.$response."\n\n";
        }
        if (self::$debugLog && ($path !== null)) {
            file_put_contents($path.'/'.self::$debugLogFile, "RESPONSE: {$response}\n\n", FILE_APPEND | LOCK_EX);
        }
    }

    public static function printPostData(
        $post,
        $path = null,
        $cliDebug = false)
    {
        $gzip = mb_strpos($post, "\x1f"."\x8b"."\x08", 0, 'US-ASCII') === 0;
        if (PHP_SAPI === 'cli') {
            $dat = Utils::colouredString(($gzip ? 'DECODED ' : '').'DATA: ', 'yellow');
        } else {
            $dat = 'DATA: ';
        }
        if ($cliDebug) {
            echo $dat.urldecode(($gzip ? zlib_decode($post) : $post))."\n";
        }
        if (self::$debugLog && ($path !== null)) {
            file_put_contents($path.'/'.self::$debugLogFile, 'DATA: '.urldecode(($gzip ? zlib_decode($post) : $post))."\n", FILE_APPEND | LOCK_EX);
        }
    }
}
