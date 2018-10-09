<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use App\Models\Warehouse;
use App\Models\WarehouseEmployee;

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

    /**
     * Get the warehouse for the current request.
     */
    public function warehouse()
    {
        if (! is_null($this->warehouse)) {
            return $this->warehouse;
        }

        $warehouse_id = $this->getWarehouseIdForResquest();

        $warehouse = Warehouse::find($warehouse_id);

        if (! is_null($warehouse)) {
            $this->setWarehouse($warehouse);

            return $warehouse;
        }

        throw new BusinessException('仓库不存在');
    }

    public function getWarehouseIdForResquest()
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

    public function roleId()
    {
        $warehouse = $this->warehouse();

        if (! is_null($this->role_id)) {
            return $this->role_id;
        }

        $employee = WarehouseEmployee::where('warehouse_id', $warehouse->id)->where('user_id',$this->id())->first(['role_id']);

        if (! $employee) {
            throw new BusinessException('无权限操作此仓库');
        }

        return $this->role_id = $employee->role_id; 
    }

    public function isRenter()
    {
        return $this->roleId() == WarehouseEmployee::ROLE_RENTER;
    }
}
