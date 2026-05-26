<?php

namespace Modules\Identity\Exceptions;

use Exception;

class ModuleException extends Exception 
{
    protected int $statusCode = 400;
    
    public function getStatusCode(): int 
    {
        return $this->statusCode;
    }
}
