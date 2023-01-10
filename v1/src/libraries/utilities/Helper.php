<?php

namespace v1\Libraries\Utilities;

use Ramsey\Uuid\Uuid;

use DateTime;
use DateTimeZone;
use PDO;
use rand;

class Helper
{

    public static function generateNumericOTP($n)
    {
        // Taking a generator string that consists of
        // all the numeric digits
        $generator = "1357902468";

        // Iterating for n-times and pick a single character
        // from generator and append it to $result

        // Login for generating a random character from generator
        //     ---generate a random number
        //     ---take modulus of same with length of generator (say i)
        //     ---append the character at place (i) from generator to result

        $result = "";

        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, rand() % strlen($generator), 1);
        }

        // Returning the result
        return $result;
    }

    public static function getDateTime()
    {
        $timestamp = time();

        //Supported Timezones: http://php.net/manual/en/timezones.php
        $userTimezone = 'Africa/Lagos';

        $dt = new DateTime();
        // Set the timestamp
        $dt->setTimestamp($timestamp);
        // Set the timezone
        $dt->setTimezone(new DateTimeZone($userTimezone));
        // Format the date
        $date = $dt->format('Y-m-d H:i:s');

        return $date;
    }

    public static function allowedImageExt(): array
    {
        return ['jpg', 'png', 'webp', 'jpeg', 'gif', 'jfif', 'svg'];
    }

    public static function allowedFileExt(): array
    {
        return ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'jfif', 'ppt', 'pptx', 'pdf', 'doc', 'docx'];
    }

    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function isValidPassword($password)
    {
        if (!preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $password))
            return FALSE;
        return TRUE;
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyHashPassword($input, $hashed_password)
    {
        return password_verify($input, $hashed_password);
    }

    public static function compressImage($sourceUrl, $destinationUrl, $quality)
    {
        $info = getimagesize($sourceUrl);

        switch ($info['mime']) {
            case 'image/jpeg':
            case 'image/gif':
                $memoryNeeded = round(($info[0] * $info[1] * $info['bits'] * $info['channels'] / 8 + Pow(2, 16)) * 1.65);
                break;

            default:
                $memoryNeeded = round(($info[0] * $info[1] * $info['bits'] * 3 / 8 + Pow(2, 16)) * 1.65);
                break;
        }

        if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > (int) ini_get('memory_limit') * pow(1024, 2)) {
            ini_set('memory_limit', (int) ini_get('memory_limit') + ceil(((memory_get_usage() + $memoryNeeded) - (int) ini_get('memory_limit') * pow(1024, 2)) / pow(1024, 2)) . 'M');
        }
        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourceUrl);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourceUrl);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourceUrl);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourceUrl);
                break;
            default:
                $image = imagecreatefromjpeg($sourceUrl);
                break;
        }
        imagejpeg($image, $destinationUrl, $quality);
    }

    public static function convertCurrency($amount, $from_currency, $to_currency)
    {
        $apikey = 'df41f2bef7bd96942b44';

        $from_Currency = urlencode($from_currency);
        $to_Currency   = urlencode($to_currency);
        $query         = "{$from_Currency}_{$to_Currency}";

        // change to the free URL if you're using the free version
        $json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
        $obj  = json_decode($json, true);

        $val = floatval($obj["$query"]);

        $total = $val * $amount;
        return number_format($total, 2, '.', '');
    }

    public static function getOS()
    {
        $user_agent  = $_SERVER['HTTP_USER_AGENT'];
        $os_platform = "Unknown OS Platform";
        $os_array    = [
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile',
        ];

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }
        }
        return $os_platform;
    }

    public static function getBrowser()
    {
        $user_agent    = $_SERVER['HTTP_USER_AGENT'];
        $browser       = "Unknown Browser";
        $browser_array = [
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser',
        ];

        foreach ($browser_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $browser = $value;
            }
        }
        return $browser;
    }

    public static function ellipsis($string, $length, $dots)
    {
        switch ($dots) {
            case 2:
                $out = strlen($string) > $length ? substr($string, 0, $length) . ".." : $string;
                break;
            case 3:
                $out = strlen($string) > $length ? substr($string, 0, $length) . "..." : $string;
                break;
        }
        return $out;
    }

    public static function getMac()
    {
        $mac = 'UNKNOWN';
        foreach (explode("\n", str_replace(' ', '', trim(`getmac`, "\n"))) as $i) {
            if (strpos($i, 'Tcpip') > -1) {
                $mac = substr($i, 0, 17);
                break;
            }
        }
        return $mac;
    }

    public static function getUrl()
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS']) || 443 == $_SERVER['SERVER_PORT']) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public static function logSession()
    {
        global $pdo;
        $num1 = date("Y") . date("m") . date("d");
        $num2 = mt_rand(1000000000, mt_getrandmax());
        $num3 = mt_rand(1000000000, mt_getrandmax());
        $endS = $num1 . $num2 . $num3;
        $stmt = $pdo->prepare("SELECT * FROM `userlogs` WHERE `logSession` ='$endS'");
        $stmt->execute();
        $count = $stmt->rowCount();
        if ($count > 0) {
            self::logSession();
        } else {
            return $endS;
        }
    }

    public static function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public static function randomSTRING()
    {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((float) microtime() * 1000000);
        $i    = 0;
        $pass = '';
        while ($i <= 16) {
            $num  = rand() % 33;
            $tmp  = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }

    public static function generateUniqueRef(string $tableName, string $uniqueColumn): string
    {
        global $pdo;
        $endString = Uuid::uuid4();

        $stmt = $pdo->prepare("SELECT * FROM `$tableName` WHERE `$uniqueColumn` ='$endString'");
        $stmt->execute();
        $count = $stmt->rowCount();
        if ($count > 0) {
            self::generateUniqueRef($tableName, $uniqueColumn);
        } else {
            return $endString;
        }
    }

    public static function check_file_exist($url)
    {
        $handle = @fopen($url, 'r');
        if (!$handle) {
            return false;
        } else {
            return true;
        }
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function getUserIp()
    {
        switch (true) {
            case (!empty($_SERVER['HTTP_X_REAL_IP'])):
                return $_SERVER['HTTP_X_REAL_IP'];
            case (!empty($_SERVER['HTTP_CLIENT_IP'])):
                return $_SERVER['HTTP_CLIENT_IP'];
            case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])):
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            default:
                return $_SERVER['REMOTE_ADDR'];
        }
    }

    public static function createSlug($string)
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($string)));
        $slug = ltrim($slug, '-');
        $slug = rtrim($slug, '-');
        return $slug;
    }

    public static function PostCurl(string $url, string $data_string, array $headers, $method = "POST")
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_FAILONERROR, 1);

        $output = curl_exec($ch);
        //var_dump($output, curl_error($ch));die();
        curl_close($ch);
        return $output;
    }

    public static function urlGetContents($url)
    {
        if (!function_exists('curl_init')) {
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function isConnected()
    {
        $connected = @fsockopen("www.example.com", 80);
        $isConn    = false;

        if ($connected) {
            $isConn = true;
            fclose($connected);
        }
        return $isConn;
    }

    public static function alphanumeric(string $data): string
    {
        return preg_replace("/[^a-zA-Z0-9]+/", "", $data);
    }

    public static function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    public static function formatDate($date)
    {
        if (self::isValidTimeStamp($date)) {
            $dt = date('Y-m-d h:i:s', $date);
            return date('F j, Y', strtotime($dt));
        }
        return date('F j, Y', strtotime($date));
    }

    public static function formatMYUnix(string $datetime): string
    {
        $ts = gmdate("Y-m-d\TH:i:s\Z", $datetime);
        return date('M Y', strtotime($ts));
    }

    public static function timeLeft($timestamp)
    {
        $ts    = gmdate("Y-m-d\TH:i:s\Z", $timestamp);
        $date  = DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $ts);
        $date2 = new \DateTime();

        return $diff = $date2->diff($date)->format("%a days, %h hours");
    }

    public static function formatShortDate(string $date): string
    {
        return date('j M, Y', strtotime($date));
    }

    public static function formatShortDateUnix($datetime)
    {
        $ts = gmdate("Y-m-d\TH:i:s\Z", $datetime);
        return date('j M, Y', strtotime($ts));
    }

    public static function formatMonthYear($date)
    {
        return date('M Y', strtotime($date));
    }

    public static function formatLongDate($date)
    {
        return date('D, F jS, Y', strtotime($date));
    }

    public static function formatTime($time)
    {
        return date('g:i A', strtotime($time));
    }

    public static function formatDateTime($date)
    {
        return date('F j, Y -  g:i A', strtotime($date));
    }

    public static function getToken($length = 10)
    {
        $characters       = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function timeAgo($datetime)
    {
        $time    = strtotime($datetime);
        $current = time();
        $seconds = $current - $time;
        $minutes = round($seconds / 60);
        $hours   = round($seconds / 3600);
        $months  = round($seconds / 2600640);

        if ($seconds <= 59) {
            if (0 == $seconds) {
                return 'now';
            } else {
                return $seconds . 's';
            }
        } else if ($minutes < 60) {
            return $minutes . 'm';
        } else if ($hours < 24) {
            return $hours . 'h';
        } else if ($months < 12) {
            return date('M j', $time);
        } else {
            return date('M j, Y', $time);
        }
    }

    public static function timeAgo2($time_ago)
    {
        $time_ago = strtotime($time_ago);

        $cur_time     = time();
        $time_elapsed = $cur_time - $time_ago;
        $seconds      = $time_elapsed;
        $minutes      = round($time_elapsed / 60);
        $hours        = round($time_elapsed / 3600);
        $days         = round($time_elapsed / 86400);
        $weeks        = round($time_elapsed / 604800);
        $months       = round($time_elapsed / 2600640);
        $years        = round($time_elapsed / 31207680);
        // Seconds
        if ($seconds <= 60) {
            return "just now";
        }
        //Minutes
        else if ($minutes <= 60) {
            if (1 == $minutes) {
                return "one minute ago";
            } else {
                return "$minutes minutes ago";
            }
        }
        //Hours
        else if ($hours <= 24) {
            if (1 == $hours) {
                return "an hour ago";
            } else {
                return "$hours hrs ago";
            }
        }
        //Days
        else if ($days <= 7) {
            if (1 == $days) {
                return "yesterday";
            } else {
                return "$days days ago";
            }
        }
        //Weeks
        else if ($weeks <= 4.3) {
            if (1 == $weeks) {
                return "a week ago";
            } else {
                return "$weeks weeks ago";
            }
        }
        //Months
        else if ($months <= 12) {
            if (1 == $months) {
                return "a month ago";
            } else {
                return "$months months ago";
            }
        }
        //Years
        else {
            if (1 == $years) {
                return "one year ago";
            } else {
                return "$years years ago";
            }
        }
    }

    public static function timeElapsed($datetime, $full = false)
    {
        $ts   = gmdate("Y-m-d\TH:i:s\Z", $datetime);
        $now  = new \DateTime;
        $ago  = new \DateTime($ts);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    public static function timePassed($timestamp)
    {
        $date   = gmdate("Y-m-d\TH:i:s\Z", $timestamp);
        $months = [];
        for ($i = 1; $i < 13; $i++) {
            $month = date('F', mktime(0, 0, 0, $i));
            $months += [substr($month, 0, 3) => $i];
        }
        $date_year    = date('Y', strtotime($date)); //year of the date
        $date_month   = date('m', strtotime($date)); //month of the date
        $date_day     = date('d', strtotime($date)); //day of the date
        $date_hour    = date('h', strtotime($date)); //hour of the date
        $date_minute  = date('i', strtotime($date)); //minute of the date
        $current_year = date('Y'); //current year(2021 in this case)
        $hour_24      = date('H', strtotime($date)); // Hour in 24hr

        //seconds passed between the given and current date
        $seconds_passed = round((time() - strtotime($date)), 0);

        //minutes  passed between the given and current date
        $minutes_passed = round((time() - strtotime($date)) / 60, 0);

        //hours passed between the given and current date
        $hours_passed = round((time() - strtotime($date)) / 3600, 0);

        //days passed between the given and current date
        $days_passed = round((time() - strtotime($date)) / 86400, 0);

        if ($seconds_passed < 60) {
            echo $seconds_passed . " second" . ((1) == $seconds_passed ? " " : "s") . " ago";
        }

        //outputs 1 second / 2-59 seconds ago

        else if ($seconds_passed >= 60 && $minutes_passed < 60) {
            echo $minutes_passed . " minute" . ((1) == $minutes_passed ? " " : "s") . " ago";
        }

        //outputs 1 minute/ 2-59 minutes ago

        else if ($minutes_passed >= 60 && $hours_passed < 24) {
            echo $hours_passed . " hour" . ((1) == $hours_passed ? " " : "s") . " ago";
        }

        //outputs 1 hour / 2-23 hours ago

        else if ($hours_passed >= 24 && $days_passed < 2) {
            echo "Yesterday at " . $date_hour . ":" . $date_minute;
            echo $hour_24 < (12) ? " AM" : " PM";
        }

        //outputs [Yesterday at 11:30] for example

        else {
            if ($current_year != $date_year) {
                foreach ($months as $month_name => $month_number) {
                    if ($month_number == $date_month) {
                        echo $month_name . " " . $date_day . ", " . $date_year . " " . $date_hour . ":" . $date_minute;
                        echo $hour_24 < (12) ? " AM" : " PM";
                        //outputs [Dec 11, 2018 11:32] for example
                    }
                }
            } else {
                foreach ($months as $month_name => $month_number) {
                    if ($month_number == $date_month) {
                        echo $month_name . " " . $date_day . ", " . $date_hour . ":" . $date_minute;
                        echo $hour_24 < (12) ? " AM" : " PM ";
                        //outputs [Dec 11, 11:32] for example
                    }
                }
            }
        }
    }
}
