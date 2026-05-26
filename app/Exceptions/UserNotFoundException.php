<?php

namespace Modules\Identity\Exceptions;

class UserNotFoundException extends ModuleException 
{
    protected int $statusCode = 404;
    
    public function __construct(string $message = "User not found") 
    {
        parent::__construct($message);
    }
}
