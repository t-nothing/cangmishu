<?php
/**
 * Created by PhpStorm.
 * User: lym
 * Date: 2018/5/23
 * Time: 11:26
 */

namespace App\Services;

use App\Mail\ExpirationWarningMail;
use App\Mail\InventoryWarningMail;
use App\Models\ProductStock;
use App\Models\User;
use App\Models\UserCategoryWarning;
use App\Models\UserExpirationWarning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WarningService
{
    /**
     * 出库完成库存报警
     *
     * @param  productStockId
     * @return void
     */
    public function afterPick($productStockId)
    {
        $this->inventoryWarning($productStockId);
    }

    /**
     * 库存预警
     *
     * @param  productStockId
     * @return void
     */
    public function inventoryWarning($productStockId)
    {
        $user = User::find(Auth::id());
        $toMail = $user->warning_email;
        $globalWarningStock = $user->default_warning_stock ? $user->default_warning_stock : 0;

        $productStock = ProductStock::where('id', $productStockId)->with('spec.product.category')->first();
        $categoryId = $productStock->spec->product->category->id;
        $warningStock = UserCategoryWarning::where('user_id', Auth::id())->where('category_id', $categoryId)->first();

        // 如果分类有设置预警值，那么就用该分类的预警值，否则就用默认的全局预警值。
        $warningStock = $warningStock ? $warningStock->warning_stock : $globalWarningStock;

        // 当商品的库存小于等于预警值，则发送预警邮件。
        if ($toMail && $productStock->stockin_num <= $warningStock) {
            $name = $productStock->spec->product->name_cn . ',规格: ' . $productStock->spec->name_cn;
            $stock = $productStock->stockin_num;
            $message = new InventoryWarningMail($toMail, $name, $stock);
            $message->onQueue('emails');

            Mail::send($message);
        }
    }

    /**
     * 保质期预警
     *
     * @param
     * @return void
     */
    public function expirationWarning()
    {
        if ($productStock = ProductStock::with('spec.product.category')->where('expiration_date', '<>', null)->where('stockin_num', '>', 0)->get()) {
            $productStock = $productStock->toArray();
            foreach ($productStock as $val) {
                $user = User::find($val['spec']['owner_id']);
                $toMail = $user->warning_expiration_email;
                $globalWarningExpiration = $user->default_warning_expiration ? $user->default_warning_expiration : 0;

                $categoryId = $val['spec']['product']['category']['id'];
                $warningExpiration = UserExpirationWarning::where('user_id', $val['spec']['owner_id'])->where('category_id', $categoryId)->first();

                // 如果分类有设置预警值，那么就用该分类的预警值，否则就用默认的全局预警值。
                $warningExpiration = $warningExpiration ? $warningExpiration->warning_expiration : $globalWarningExpiration;

                // 当商品的到达预警值，则发送预警邮件。
                if ($toMail && time() >= (strtotime($val['expiration_date']) - $warningExpiration * 86400)) {
                    $name = $val['spec']['product']['name_cn'] . ',规格: ' . $val['spec']['name_cn'];
                    $expirationDate = $val['expiration_date'];
                    $message = new ExpirationWarningMail($toMail, $name, $expirationDate);
                    $message->onQueue('emails');

                    Mail::send($message);
                }

            }
        }
    }

}
