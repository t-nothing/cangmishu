<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $connection = 'mysql';

    protected $perPage = 10;

    protected static $_instance = [];
    /**
     * 获得单实例
     * @return static
     */
    public static function getIns(){
        $class = get_called_class();
        if (!array_key_exists($class,self::$_instance)) {
            self::$_instance[$class] = new static();
            return self::$_instance[$class];
        }
        return self::$_instance[$class];
    }


    public static function formatPaged($page, $size, $total)
    {
        return [
            'total' => $total,
            'page' => $page,
            'size' => $size,
            'more' => ($total > $page * $size) ? 1 : 0
        ];
    }

    public static function formatBody(array $data = [])
    {
        $data['error_code'] = 0;
        return $data;
    }

    public static function formatError($code, $message = null)
    {
        switch ($code) {
            case self::UNKNOWN_ERROR:
                $message = trans('message.error.unknown');
                break;
            
            case self::NOT_FOUND:
                $message = trans('message.error.404');
                break;
        }

        $body['error'] = true;
        $body['error_code'] = $code;
        $body['error_desc'] = $message;

        return $body;
    }

    /**
     * 标准返回格式
     * @param int $ret
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function formatResponse($ret = 1, $msg = '', $data = [])
    {
        return array('ret' => $ret, 'msg' => $msg, 'data' => $data);
    }

}