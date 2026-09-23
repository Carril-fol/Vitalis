<?php
namespace App\Turns\Models;

enum TurnStatus: string
{
    case Booked = 'booked';
    case CheckedIn = 'checked_in';
    case Attended = 'attended';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';
}
