<?php

namespace Modules\Identity\Exceptions;

class InvalidCurrentPasswordException extends ModuleException
{
    protected int $statusCode = 422;

    public function __construct(string $message = 'Current password is invalid.')
    {
        parent::__construct($message);
    }
}
