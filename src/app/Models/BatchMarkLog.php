<?php 

namespace App\Models;  

use Illuminate\Support\Facades\Redis;

class BatchMarkLog extends Model 
{
	protected $table = 'batch_mark_log';

	 protected $fillable = [
		 'warehouse_code',
		 'batch_mark',
	 ];

	static function newMark($warehouse_code)
	{
		$prefix = 'cangmishu_';
		$redis_key = $prefix.$warehouse_code;
		$is_exists = Redis::Exists($redis_key);
		if(!$is_exists){
			$batch_mark = BatchMarkLog::where('warehouse_code',$warehouse_code)->orderBy('id')->pluck('batch_mark')->first();	  
			$batch_mark = empty($batch_mark) ? 1 :$batch_mark;
			Redis::set($redis_key,$batch_mark);
		}
		 return Redis::Incr($redis_key);
	}

	static function saveBatchCode($batch)
	{
	$batch_code =  $batch->batch_code;
	$batch_mark =  base_convert(substr($batch_code,-4),16,10);
	$data = [
		'warehouse_code' => app('auth')->warehouse()->code,
		'batch_mark'	 => $batch_mark,
	];
	$batchMark = new BatchMarkLog();
	$batchMark::binds($batchMark,$data);
	$batchMark->save();
	}
}
?>
