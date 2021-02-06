<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Distributor extends Model
{
    use SoftDeletes;
    protected $table ="distributor";
    protected $fillable =['user_id','name_cn','name_en'];
    protected $guarded = [];
    protected  $appends=['name'];

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }

    public function  ScopeWhose($query, $owner_id){
        return $query->where('user_id',$owner_id);
    }

    public function  ScopeHasKeywords($query, $keywords){
        return $query->where('name_cn','like','%'.$keywords.'%')->orWhere('name_en','like','%'.$keywords.'%');
    }



    public function getNameAttribute()
    {
        $lang = app('translator')->getLocale();
        if(in_array($lang,['en','cn','zh-CN'])){
            if(in_array($lang,['cn','zh-CN'])){
                $lang = "cn";
            }
        }else{
            $lang = "cn";
        }
        $name = "name_".$lang;
        return $this->$name;
    }
}



