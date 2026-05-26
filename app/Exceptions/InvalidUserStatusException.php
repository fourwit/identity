<?php

namespace Modules\Identity\Exceptions;

class InvalidUserStatusException extends ModuleException 
{
    protected int $statusCode = 422;

    public function __construct(string $message = "Invalid user status") 
    {
        parent::__construct($message);
    }
}
