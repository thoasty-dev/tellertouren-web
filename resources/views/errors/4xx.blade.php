@extends('errors.layout', [
    'code' => $exception->getStatusCode(),
    'headline' => 'Diese Anfrage konnten wir nicht verarbeiten',
    'message' => 'Der Link ist womöglich fehlerhaft. Nutze die Navigation oder die Suche oben.',
])
