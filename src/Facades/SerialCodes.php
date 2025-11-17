<?php

namespace Verifarma\SerialCodesGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class SerialCodes extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'serial-codes-generator';
    }
}
