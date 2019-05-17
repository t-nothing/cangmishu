<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseController extends Model
{
    use SoftDeletes;

    protected $table ="warehouse";
    protected $guarded = [];

    public $hidden= [
      'deleted_at'
    ];


    public function fromDateTime($value)
    {
        return parent::fromDateTime($value);
    }


    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'owner_id', 'id');
    }
}
