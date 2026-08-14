<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN = 'ADMIN';
    case TEACHER = 'TEACHER';
    case ACCOUNTANT = 'ACCOUNTANT';
    case LIBRARIAN = 'LIBRARIAN';
    case STUDENT = 'STUDENT';
    case PARENT = 'PARENT';
}