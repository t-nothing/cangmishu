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

class SkuMarkLog extends Model 
{
	protected $table = 'sku_mark_log';
	 protected $fillable = [
	 	'warehouse_code',
		'spec_id',
		'sku_mark',
	];

	static function saveSkuLog($spec)
	{
		$data = [
			'warehouse_code' => app('auth')->warehouse()->code,
			'spec_id'	 => $spec->spec_id,
			'sku_mark'	 => base_convert(substr($spec->sku,-4),16,10), 
		];

	    $skuLog = new SkuMarkLog();
        $skuLog->warehouse_code = $data['warehouse_code'];
        $skuLog->spec_id = $data['spec_id'];
        $skuLog->sku_mark = $data['sku_mark'];
	  	return  $skuLog->save();

	}
}
?>
