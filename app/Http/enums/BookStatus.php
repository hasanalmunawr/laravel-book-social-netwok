<?php

namespace App\Http\enums;

enum BookStatus:string
{
    case Available = 'available';
    case Borrowed = 'borrowed';
    case Archived = 'archived';
}
