<?php

declare(strict_types=1);

namespace Roster\Enums;

use Roster\Traits\EnumValues;

/**
 * Represents the type of activity that can be scheduled.
 *
 * This enum defines all possible activity types that can be associated
 * with schedule items in the roster system. Each activity type corresponds
 * to a specific kind of professional engagement or event.
 */
enum ActivityType: string
{
    use EnumValues;

    case CONSULTATION = 'consultation';
    case TRAINING = 'training';
    case MEETING = 'meeting';
    case COACHING = 'coaching';
    case APPOINTMENT = 'appointment';
    case WORKSHOP = 'workshop';
    case SEMINAR = 'seminar';
    case INTERVIEW = 'interview';
    case EXAMINATION = 'examination';
    case THERAPY = 'therapy';
    case TUTORING = 'tutoring';
    case COURSE = 'course';
    case LECTURE = 'lecture';
    case REHEARSAL = 'rehearsal';
    case COMPETITION = 'competition';
    case GAME = 'game';
    case EVENT = 'event';
    case MAINTENANCE = 'maintenance';
    case INSPECTION = 'inspection';
    case DELIVERY = 'delivery';
    case SERVICE = 'service';
    case CONSULTANCY = 'consultancy';
    case CHECKUP = 'checkup';
    case AUDIT = 'audit';
    case OTHER = 'other';
}
