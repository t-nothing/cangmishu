<?php

namespace App\Models;

use App\Models\Model;

class Kep extends Model
{
    protected $table = 'kep';
    protected $guarded = [];

    /**
     * 获取单条数据
     * @param $id
     * @return bool
     */
    public function getOne($id){
        $data=$this->where('id',$id)->first();
        return $data?$data->toArray():false;
    }

    /**
     * 篮子名获取单条数据
     * @param $name
     * @return bool
     */
    public function nameGetOne($name){
        $data=$this->where('code',$name)->first();
        return $data?$data->toArray():false;
    }

    /**
     * 绑定捡货单
     * @param $name
     * @param $shipment_num
     * @return mixed
     */
    public function binding($name,$shipment_num){
        $update=[
            'shipment_num'  =>  $shipment_num
        ];
        return $this->where('code',$name)->update($update);
    }

    /**
     * 释放篮子
     * @param $name
     * @return mixed
     */
    public function release($name){
        if($name=='all'){
            return $this->where('shipment_num','<>','')->update(['shipment_num'=>'']);
        }else{
            return $this->where('code',$name)->update(['shipment_num'=>'']);
        }
    }

    /**
     * 捡货单号释放篮子
     * @param $shipment_num
     * @return mixed
     */
    public function shipmentRelease($shipment_num){
        return $this->where('shipment_num',$shipment_num)->update(['shipment_num'=>'']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * 限制查询只包括指定关键字。
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author liusen
     */
    public function scopeHasKeyword($query, $keywords)
    {
        return $query->where('code', 'like', '%' . $keywords . '%');
    }
}
