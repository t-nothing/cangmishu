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

class SubscribeMessageConfig extends Model
{
    public const TYPE_ORDER_NOTIFY = 1;
    public const TYPE_STOCK_WARNING = 2;
    public const TYPE_DAILY_REPORT = 3;

    public const CHANNEL_WECHAT = 1;
    public const CHANNEL_EMAIL = 2;
    public const CHANNEL_PHONE = 3;

    public const STATUS_ENABLE = 1;
    public const STATUS_DISABLE = 0;

    protected  $guarded = [];

    /**
     * 所有类型
     *
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ORDER_NOTIFY => __('开启下单通知'),
            self::TYPE_STOCK_WARNING=> __('开启库存预警通知'),
            self::TYPE_DAILY_REPORT=> __('开启经营日报'),
        ];
    }

    /**
     * 用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $channel
     * @return \Illuminate\Support\Collection
     */
    public static function getChannelConfig($channel)
    {
        $data = self::query()->where('user_id', auth()->id())
            ->where('channel', $channel)
            ->get();

        return collect(self::getTypes())->map(function ($value, $key) use ($data) {
            return [
                'type' => $key,
                'name' => $value,
                'status' => $data->firstWhere('type', $key)->status ?? 0,
            ];
        })->values();
    }

    /**
     * @param $channel
     * @param  array  $data
     * @return bool
     */
    public static function updateChannelConfig($channel, array $data)
    {
        collect($data)->each(function ($value) use ($channel) {
            return self::query()->updateOrCreate(
                [
                    'type' => $value['type'],
                    'channel' => $channel,
                    'user_id' => auth()->id(),
                ],
                [
                    'status' => $value['status'],
                ]);
        });

       return true;
    }

    /**
     * Convert a DateTime to a storable string.
     *
     * @param  mixed  $value
     * @return string|null
     */
    public function fromDateTime($value)
    {
        return empty($value) ? $value : $this->asDateTime($value)->format(
            $this->getDateFormat()
        );
    }
}
