<?php

return [
    'email_enabled'             => filter_var(env('APPROVAL_EMAIL_NOTIFICATIONS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'signed_link_ttl_hours'     => (int) env('APPROVAL_EMAIL_SIGNED_LINK_TTL_HOURS', 72),
    'reminder_enabled'          => filter_var(env('APPROVAL_EMAIL_REMINDER_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'reminder_hours_before_due' => (int) env('APPROVAL_EMAIL_REMINDER_HOURS_BEFORE_DUE', 24),
    'max_reminders'             => (int) env('APPROVAL_EMAIL_MAX_REMINDERS', 3),
];
