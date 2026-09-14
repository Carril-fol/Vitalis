<?php
namespace App\Users\Models;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';   
}
