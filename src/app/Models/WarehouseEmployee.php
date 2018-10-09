<?php

namespace App\Models;

class WarehouseEmployee extends Model
{
    protected $table = 'warehouse_employee';
    
    public $timestamps = false;

    const ROLE_ADMIN = 1;        //仓储系统管理员
    const ROLE_OWNER = 2;       //仓库产权方
    const ROLE_RENTER = 3;      //仓库租赁方
    
    const ROLE_ARR = [
		    'admin' => self::ROLE_ADMIN,
		    'owner' => self::ROLE_OWNER,
		    'renter'=> self::ROLE_RENTER,
		   ];   

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    protected $fillable = [
        'warehouse_id',
        'user_id',
        'role_id',
        'operator',
    ];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse','warehouse_id','id');
    }
	
   static function NameToId($data)
  {
	$role_ids = [];	
	foreach($data as $k=>$v){
		$role_ids[$k] = self::ROLE_ARR[$v];
	}
	return $role_ids;
  }

}
