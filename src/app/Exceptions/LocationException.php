<?php

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
