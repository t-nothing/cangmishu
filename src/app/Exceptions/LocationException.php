<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Exceptions;

class LocationException extends \Exception
{
    var $locations;

    public function __construct(array $locations){
        $this->locations = $locations;
        parent::__construct(trans("message.warehouseLocationNotExistExt", [
                'code'=> implode(",", $locations)
            ]), 0);
    }   


    public function getLocations() {
        return $this->locations;
    }
}
