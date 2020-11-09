<?php

namespace App\Guard;

use App\Exceptions\BusinessException;
use App\Models\Warehouse;

/**
 * These methods are typically the same across all guards.
 */
trait WarehouseGuardHelpers
{
    /**
     * The currently chosen warehouse.
     *
     * @var \App\Models\Warehouse
     */
    protected $warehouse;

    protected $role_id;

    protected  $owner_id;

    /**
     * Get the warehouse for the current request.
     * @throws BusinessException
     */
    public function warehouse()
    {
        if (! is_null($this->warehouse)) {
            return $this->warehouse;
        }

        $warehouse_id = $this->getWarehouseIdForRequest();

        $warehouse = Warehouse::find($warehouse_id);

        if (! is_null($warehouse)) {
            $this->setWarehouse($warehouse);

            return $warehouse;
        }

        throw new BusinessException('请选择仓库');
    }

    /**
     * @return mixed
     * @throws BusinessException
     */
    public function getWarehouseIdForRequest()
    {
        $warehouse_id = $this->getRequest()->header('Warehouse');
        if (! is_null($warehouse_id)) {
            return $warehouse_id;
        }

        $warehouse_id = $this->request->query('warehouse_id');
        if (! is_null($warehouse_id)) {
            return $warehouse_id;
        }

        $warehouse_id = $this->request->input('warehouse_id');
        if (! is_null($warehouse_id)) {
            return $warehouse_id;
        }

        throw new BusinessException('请选择仓库');
    }

    /**
     * Set the current user.
     *
     * @return $this
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * 判断当前账户是否为员工账户
     *
     * @return mixed
     */
    public function ownerId()
    {
        $user = $this->user();
        $owner= $user->boss_id ?:$user->id;
        return $owner;
    }

    /**
     * @return int
     */
    public function warehouseId()
    {
        //判断当前账户是否为员工账户
        return $this->user()->default_warehouse_id ?? 0;
    }

    /**
     * @return mixed
     */
    public function shopId()
    {
        return $this->getRequest()->header('Shop', '');
    }
}
