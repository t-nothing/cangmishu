<?php

namespace App\Models;


class UserCategoryWarning extends Model
{
	protected $table = 'user_category_warning';
	protected $fillable = [
		'user_id',
		'category_id',
		'warning_stock'
	];
}
