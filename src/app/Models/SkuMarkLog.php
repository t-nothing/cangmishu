<?php 

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
