@extends('errors.layout', [
    'code' => 429,
    'headline' => 'Zu viele Anfragen',
    'message' => 'Das ging uns etwas zu schnell. Warte einen Moment und probiere es dann erneut.',
])
