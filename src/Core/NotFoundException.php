<?php

namespace App\Core;

use RuntimeException;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;


#[WithHttpStatus(404)]
class NotFoundException extends RuntimeException
{
}
