<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Models\ProductSpec;
use App\Models\UserCategoryWarning;
use App\Mail\InventoryWarningMail; 
use Illuminate\Support\Facades\Mail; 

class WarningCategoryJob  extends Job
{
	/**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'wms2';

	protected $order_item;

	/**
	 *   Create a new job instance.
	 *   @return void
	 */
    public function __construct(OrderItem $order_item)
    {
 		$this->order_item = $order_item;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		app('log')->info('库存预警检查 - JOb参数', $this->order_item->toArray());
		$order_item = $this->order_item;
		$user = User::find($order_item->owner_id);

		$spec = ProductSpec::whose($user->id)
			->ofWarehouse($order_item->warehouse_id)
			->where('relevance_code',$order_item->relevance_code)
			->with('product')
			->first();

		if (! $spec) {
			app('log')->info('库存预警检查 - 规格不存在', $this->order_item->toArray());

			return;
		}

		//目前仓库库存
		$stockin_num = ProductStock::whose($user->id)
			 	->ofWarehouse($order_item->warehouse_id)
			 	->where('spec_id',$spec->id)
			 	->sum('stockin_num');
		
		$category_id = $spec->product->category_id;
		if(empty($category_id)){
			app('log')->info('库存预警检查 - 分类为空',$this->order_item->product->toArray());
			return;
		}
		 //用户预警库存
		$user_warning_stock =  UserCategoryWarning::where('user_id',$order_item->owner_id)
						->where('category_id',$category_id)
						->first();
		if(! $user_warning_stock){
			app('log')->info('库存预警检查 - 用户分类预警不存在',$this->order_item->toArray());
			return;
		}
		$warning = [];

		$warning['stock'] = $user_warning_stock->warning_stock;

		if($warning['stock'] == 0){
			  $warning['stock'] = $user->default_warning_stock;
		}

		if($warning['stock'] == 0){
			app('log')->info('库存预警检查 - 用户数据',$user->toArray());
			return ;
		}

		$warning['email'] = $user->warning_email;
		if (empty($warning['email'])){
			$warning['email'] = $user->email;
		}

		if(empty($warning['email'])){
			app('log')->info('库存预警检查 - 用户数据',$user->toArray());
			return;
		}

		app('log')->info('库存预警检查 - 库存预警数据',$warning);

		if (!empty($warning['email']) && $warning['stock'] > 0){
			if ($stockin_num < $warning['stock']){

			   	 app('log')->info('库存预警检查 - 库存小于预警数量');
				$name = $spec->product->name_cn.'规格'.$spec->name_cn;
				$message = new InventoryWarningMail($warning['email'], $name, $stockin_num);
				$message->onQueue('emails');
				Mail::send($message);
			}
		}
	}
}
