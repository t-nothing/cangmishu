<?php

use App\Exceptions\BusinessException;

if (! function_exists('formatRet')) {
    /**
     * Return a new JSON response from the application.
     *
     * @param  int    $code
     * @param  string $message
     * @param  array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    function formatRet($code, $message = '', array $data = [], $status = 200, array $headers = [], $options = 0)
    {
        if($code == 0 && !$message){
            $message = "成功";
        }

        if($code !=0 && !$message){
            $message = "失败";
        }
        $rt = [
            'status' => $code,
            'msg' => $message,
            'data' => $data,
        ];

        $options = JSON_UNESCAPED_UNICODE;

        return response()->json($rt, $status, $headers, $options);
    }
}

if (! function_exists('formatRetImmediately')) {
    /**
     * @param  string $message
     *
     * @throws App\Exception\BusinessException
     */
    function eRet($message = '')
    {
        throw new BusinessException($message);
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return app()->basePath() . DIRECTORY_SEPARATOR . 'public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (! function_exists('mb_str_split')) {
    /**
     * 拆分中文
     * @param $str
     * @return array
     */
    function mb_str_split($str)
    {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }
}

if (! function_exists('birthday')) {
    /**
     * 获取年龄
     * @param $birthday
     * @return bool|int
     */
    function birthday($birthday){
        $age = strtotime($birthday);
        if($age === false){
            return false;
        }
        list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age));
        $now = strtotime("now");
        list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now));
        $age = $y2 - $y1;
        if((int)($m2.$d2) < (int)($m1.$d1))
            $age -= 1;
        return $age;
    }
}

if (! function_exists('encodeData')) {
    /**
     * @param  string  $endDate
     * @return string
     */
    function encodeData($endDate)
    {
        $hashTab = [
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 'A',
            11 => 'B',
            12 => 'C',
            13 => 'D',
            14 => 'E',
            15 => 'F',
            16 => 'G',
            17 => 'H',
            18 => 'J',
            19 => 'K',
            20 => 'L',
            21 => 'M',
            22 => 'N',
            23 => 'O',
            24 => 'P',
            25 => 'Q',
            26 => 'R',
            27 => 'S',
            28 => 'T',
            29 => 'U',
            30 => 'V',
            31 => 'W',
            32 => 'X',
            33 => 'Y',
            34 => 'Z',
        ];

        // 参考值
        $startDate = "2017-01-01";

        $diffDate = floor((strtotime($endDate) - strtotime($startDate)) / 86400);
        // 转换进制
        $high = intval($diffDate / 100);
        $low = $diffDate % 100;

        if (array_key_exists($high, $hashTab)) {
            $hashHigh = $hashTab[$high];
        } else {
            return "ERROR";
        }

        return sprintf("%s%02d", $hashHigh, $low);
    }
}

if (! function_exists('encodeseq')) {
    /**
     * @param  string  $num
     * @return string
     */
    function encodeseq($num)
    {
        // 10000 => A000
        $hashTab = [
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 'A',
            11 => 'B',
            12 => 'C',
            13 => 'D',
            14 => 'E',
            15 => 'F',
            16 => 'G',
            17 => 'H',
            18 => 'J',
            19 => 'K',
            20 => 'L',
            21 => 'M',
            22 => 'N',
            23 => 'O',
            24 => 'P',
            25 => 'Q',
            26 => 'R',
            27 => 'S',
            28 => 'T',
            29 => 'U',
            30 => 'V',
            31 => 'W',
            32 => 'X',
            33 => 'Y',
            34 => 'Z',
        ];

        $high = intval($num % 34);
        $low = $num % 1000;

        if (array_key_exists($high, $hashTab)) {
            $hashHigh = $hashTab[$high];
        } else {
            return "ERROR";
        }

        return sprintf("%s%03d", $hashHigh, $low);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('random_string'))
{
    /**
     * Create a Random String
     *
     * Useful for generating passwords or hashes.
     *
     * @param   string  type of random string.  basic, alpha, alnum, numeric, nozero, unique, md5, encrypt and sha1
     * @param   int number of characters
     * @return  string
     */
    function random_string($type = 'alnum', $len = 8)
    {
        switch ($type)
        {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique': // todo: remove in 3.1+
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt': // todo: remove in 3.1+
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }
}
