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

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Laravel\Passport\HasApiTokens;

class ShopUser extends User
{
    use HasApiTokens,Notifiable;

    protected $table = 'shop_user';

    protected  $dateFormat  = "U";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'email', 'password', 'weapp_openid', 'nick_name', 'avatar_url', 'last_login_ip', 'last_login_time',
        'mobile', 'gender', 'country', 'province', 'city', 'language', 'weapp_session_key'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','openid','deleted_at',
    ];

    /**
     * 用戶收藏的商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function collectShopProduct()
    {
        return $this->belongsToMany(
            ShopProduct::class,
        'shop_product_collection',
        'user_id',
        'shop_product_id'
        );
    }

    /**
     * 地址
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(ShopUserAddress::class, 'shop_user_id', 'id');
    }

    //修改电话信息
    public function userInfo()
    {
        $q = Auth::user()->openid;

        return self::where('openid', $q)->get();
    }

    public function findForPassport($username) {
        return $this->where('openid', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        $decrypted = Crypt::decryptString($password);
        if ($decrypted == $this->openId) {
            return true;
        }
        return false;
    }
}
