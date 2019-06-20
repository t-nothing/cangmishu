<?php

namespace App\Models;

class VerifyCode extends Model
{
    protected $table = 'verify_code';

    protected  $fillable = ['email','code','expired_at'];

    public  $timestamps = true;

    public function fromDateTime($value) {

        return parent::fromDateTime($value);
    }

}