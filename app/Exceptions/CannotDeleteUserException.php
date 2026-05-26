<?php

namespace Modules\Identity\Exceptions;

class CannotDeleteUserException extends ModuleException 
{
    protected int $statusCode = 403;
    
    public function __construct(string $message = "Cannot delete this user") 
    {
        parent::__construct($message);
    }
}
