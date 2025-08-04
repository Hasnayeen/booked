<?php

declare(strict_types=1);

namespace App\Casts;

use App\ValueObjects\SeatConfiguration;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class SeatConfigurationCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?SeatConfiguration
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            throw_if(json_last_error() !== JSON_ERROR_NONE, new InvalidArgumentException('Invalid JSON in seat configuration: ' . json_last_error_msg()));

            $value = $decoded;
        }

        if (! is_array($value)) {
            return null;
        }

        try {
            return SeatConfiguration::fromArray($value);
        } catch (InvalidArgumentException $e) {
            // Log the error and return null to handle gracefully
            logger()->warning('Invalid seat configuration data', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
                'data' => $value,
            ]);

            return null;
        }
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof SeatConfiguration) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            try {
                $seatConfig = SeatConfiguration::fromArray($value);

                return json_encode($seatConfig->toArray());
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException('Invalid seat configuration array: ' . $e->getMessage(), $e->getCode(), $e);
            }
        }

        throw new InvalidArgumentException('Seat configuration must be a SeatConfiguration instance, array, or null');
    }
}
