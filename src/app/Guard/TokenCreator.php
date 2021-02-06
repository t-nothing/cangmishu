<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Guard;

use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class TokenCreator
{
    /**
     * 生成一个新的 token，token 哈希来保证唯一性。
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param $type
     * @return \App\Models\Token|null
     */
    public function create(AuthenticatableContract $user, $type)
    {
        $token = new Token;
        $token->token_type = $type;
        $token->token_value = hash_hmac('sha256', $user->getAuthIdentifier().microtime(), config('APP_KEY'));
        $token->expired_at = Carbon::now()->addWeek();
        $token->owner_user_id = $user->getAuthIdentifier();
        $token->is_valid = Token::VALID;

        if ($token->save()) {
            return $token;
        }

        return null;
    }
}
