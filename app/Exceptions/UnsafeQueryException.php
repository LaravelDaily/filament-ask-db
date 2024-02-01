<?php

namespace App\Exceptions;

use Exception;

class UnsafeQueryException extends Exception
{
    public static function fromQuery(string $query): self
    {
        return new self("The query `{$query}` is not safe to run.");
    }
}