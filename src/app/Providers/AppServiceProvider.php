<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
	/**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    	// 手机号校验
		// Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
		//     $code = $validator->getValue('phone_area_code');

		//     switch ($code) {
		//         case '0031':
		//             // 荷兰 9 位数
		//             $this->digits = 9;
		//             return $validator->validateDigits($attribute, $value, [9]);
		//             break;
		        
		//         case '0032':
		//             // 比利时 9 位数
		//             $this->digits = 9;
		//             return $validator->validateDigits($attribute, $value, [9]);
		//             break;

		//         case '0049':
		//             // 德国 10 位数
		//             $this->digits = 10;
		//             return $validator->validateDigits($attribute, $value, [10]);
		//             break;

		//         default:
		//             break;
		//     }

		//     return true;
		// });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}
