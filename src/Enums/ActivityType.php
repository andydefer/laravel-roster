<?php

declare(strict_types=1);

namespace Roster\Enums;

enum ActivityType: string
{
    use EnumValues;
    case CONSULTATION       = 'consultation';
    case TRAINING           = 'training';
    case MEETING            = 'meeting';
    case COACHING           = 'coaching';
    case APPOINTMENT        = 'appointment';
    case WORKSHOP           = 'workshop';
    case SEMINAR            = 'seminar';
    case INTERVIEW          = 'interview';
    case EXAMINATION        = 'examination';
    case THERAPY            = 'therapy';
    case TUTORING           = 'tutoring';
    case COURSE             = 'course'; // safe
    case LECTURE            = 'lecture';
    case REHEARSAL          = 'rehearsal';
    case COMPETITION        = 'competition';
    case GAME               = 'game';
    case EVENT              = 'event';
    case MAINTENANCE        = 'maintenance';
    case INSPECTION         = 'inspection';
    case DELIVERY           = 'delivery';
    case SERVICE            = 'service';
    case CONSULTANCY        = 'consultancy';
    case CHECKUP            = 'checkup';
    case AUDIT              = 'audit';
    case OTHER              = 'other';
}
