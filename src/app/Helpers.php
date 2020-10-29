<?php

use App\Exceptions\BusinessException;

if (! function_exists('formatRet')) {
    /**
     * Return a new JSON response from the application.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  array  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return \Illuminate\Http\JsonResponse
     */
    function formatRet(int $code, $message = '', array $data = [], $status = 200, array $headers = [], $options = 0)
    {
        if($code == 0 && !$message){
            $message = trans("message.success");
        }

        if($code !=0 && !$message){
            $message = trans("message.failed");
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

    function encodeseqExt($num, $w = 2)
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
        $low = $num % 100;

        if (array_key_exists($high, $hashTab)) {
            $hashHigh = $hashTab[$high];
        } else {
            return "ERROR";
        }

        return sprintf("%s%02d", $hashHigh, $low);
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

if (! function_exists('currency_symbol')) {
    /**
     * 转换货币符号
     * @param $birthday
     * @return string
     */
    function currency_symbol($default_currency){
        $result = "￥";
        if($default_currency == "USD") {
            $result = "$";
        }elseif($default_currency == "EUR") {
            $result = "€";
        }

        return $result;
    }
}

if (! function_exists('year_code')) {
    /**
    * 返回年代码
    */
    function year_code($y = NULL){

        if(is_null($y)) $y = date('Y');
        $year_code_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        return $year_code_arr[intval($y)-2016];
    }
}

if (! function_exists('month_code')) {
    /**
    * 返回月代码
    */
    function month_code(){
        $month_code_arr = array('A','A','B','C','D','E','F','G','H','I','J','K','L');
        return $month_code_arr[date('n')];
    }
}

if (! function_exists('day_code')) {
    /**
    * 返回日代码
    */
    function day_code(){
        $day_code_arr = array('1','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        return $day_code_arr[date('j')];
    }
}

if (! function_exists('enid')) {
    //十进制转换三十六进制
    function enid($int, $format = 8) {

        $dic = array(
        0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9',
        10 => 'A', 11 => 'B', 12 => 'C', 13 => 'D', 14 => 'E', 15 => 'F', 16 => 'G', 17 => 'H', 18 => 'I',
        19 => 'J', 20 => 'K', 21 => 'L', 22 => 'M', 23 => 'N', 24 => 'O', 25 => 'P', 26 => 'Q', 27 => 'R',
        28 => 'S', 29 => 'T', 30 => 'U', 31 => 'V', 32 => 'W', 33 => 'X', 34 => 'Y', 35 => 'Z'
        );

        $arr = array();
        $loop = true;
        while ($loop)
        {
            $arr[] = $dic[bcmod($int, 36)];
            $int = floor(bcdiv($int, 36));
            if ($int == 0) {
                $loop = false;
            }
        }
        array_pad($arr, $format, $dic[0]);
        return implode('', array_reverse($arr));
    }
}


if (! function_exists('template_download_name')) {
    /**
    * 下载文件名称
    */
    function template_download_name($v,$lang = 'cn'){

        $name = "";
        switch ($v) {
            case 'pdfs.order.template_pick':
                $name = $lang=="cn"?"拣货单":"pick";
                break;
            case 'pdfs.order.template_out':
                $name = $lang=="cn"?"出库单":"order";
                break;

            case 'pdfs.batch.template_batchno':
                $name = $lang=="cn"?"批次标签":"batchno";
                break;

            case 'pdfs.batch':
            case 'pdfs.batch.template_entry':
                $name = $lang=="cn"?"入库单":"entry";
                break;

            case 'pdfs.batch.template_purchase':
                $name = $lang=="cn"?"采购单":"purchase";
                break;

            case 'pdfs.recount':
                $name = $lang=="cn"?"盘点单":"recount";
                break;

            default:
                # code...
                break;
        }

        return $name;
    }
}


if ( ! function_exists('redirect_url'))
{
    /**
     * Header Redirect
     *
     * Header redirect in two flavors
     * For very fine grained control over headers, you could use the Output
     * Library's set_header() function.
     *
     * @param   string  $uri    URL
     * @param   string  $method Redirect method
     *          'auto', 'location' or 'refresh'
     * @param   int $code   HTTP Response status code
     * @return  void
     */
    function redirect_url($uri = '', $method = 'auto', $code = NULL)
    {
        if ( ! preg_match('#^(\w+:)?//#i', $uri))
        {
            $uri = site_url($uri);
        }

        // IIS environment likely? Use 'refresh' for better compatibility
        if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== FALSE)
        {
            $method = 'refresh';
        }
        elseif ($method !== 'refresh' && (empty($code) OR ! is_numeric($code)))
        {
            if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1')
            {
                $code = ($_SERVER['REQUEST_METHOD'] !== 'GET')
                    ? 303   // reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
                    : 307;
            }
            else
            {
                $code = 302;
            }
        }

        switch ($method)
        {
            case 'refresh':
                header('Refresh:0;url='.$uri);
                break;
            default:
                header('Location: '.$uri, TRUE, $code);
                break;
        }
        exit;
    }
}
