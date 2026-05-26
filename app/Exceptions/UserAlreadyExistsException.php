<?php

namespace Modules\Identity\Exceptions;

class UserAlreadyExistsException extends ModuleException 
{
    protected int $statusCode = 409;
    
    public function __construct(string $message = "User already exists")
    {
        parent::__construct($message);
    } 
}
