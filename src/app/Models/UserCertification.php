<?php

namespace App\Models;

class UserCertification extends Model
{
	const CHECK_STATUS_ONGOING = 1;// 待审核
	const CHECK_STATUS_SUCCESS = 2;// 通过
	const CHECK_STATUS_FAILURE = 3;// 驳回

    protected $table = 'user_certification';
}
