<?php

namespace App\Models\Traits;

trait HasUuid {
    public final const string ATTRIBUTE_UUID = 'uuid';

    final public static function bootHasUuid (): void {
        static::creating(static function ($model) {
            $model->assignUuid();
        });
    }

    final public static function findByUuid (string $uuid): ?static {
        return static::query()->whereUuid($uuid)->first();
    }

    final public static function findByUuidOrFail (string $uuid): static {
        return static::query()->whereUuid($uuid)->firstOrFail();
    }

    final public static function generateUuid (): string {
        return uuid(static::uuidPrefix());
    }

    public function assignUuid (): string {
        if (empty($this->uuid)) {
            $this->uuid = static::generateUuid();
        }

        return $this->uuid;
    }

    protected function scopeWhereUuid ($query, string $uuid): void {
        $query->where(self::ATTRIBUTE_UUID, '=', $uuid);
    }

    abstract public static function uuidPrefix (): ?string;
}
