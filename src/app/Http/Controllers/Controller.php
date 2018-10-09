<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
	protected function throwValidationException(Request $request, $validator)
	{
		$response = formatRet(422, $validator->errors()->first(), $this->formatValidationErrors($validator));

	    throw new ValidationException($validator, $response);
	}
}
