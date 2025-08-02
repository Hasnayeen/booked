<?php

namespace Database\Seeders;

use App\Enums\BusCategory;
use App\Enums\BusType;
use App\Models\Bus;
use App\Models\Operator;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operator = Operator::first();

        if (! $operator) {
            $this->command->warn('No operator found. Please run the main DatabaseSeeder first.');

            return;
        }

        $busCounter = 1;

        // Create buses for each combination of category and type
        foreach (BusCategory::cases() as $category) {
            // Determine which types to create based on category
            $typesToCreate = match ($category) {
                BusCategory::Economy => BusType::cases(), // Both AC and Non-AC
                BusCategory::Business,
                BusCategory::Luxury,
                BusCategory::Sleeper => [BusType::Ac], // Only AC
            };

            foreach ($typesToCreate as $type) {
                // Create 2 buses for each combination
                for ($i = 1; $i <= 2; $i++) {
                    $busNumber = sprintf('BUS-%03d', $busCounter);
                    $licensePrefix = $this->getLicensePlatePrefix($category, $type);
                    $licensePlate = sprintf('%s-%04d', $licensePrefix, $busCounter);

                    // Use factory with appropriate state for category
                    $factory = Bus::factory()->forOperator($operator);

                    $factory = match ($category) {
                        BusCategory::Luxury => $factory->luxury(),
                        BusCategory::Sleeper => $factory->sleeper(),
                        BusCategory::Economy, BusCategory::Business => $factory->standard(),
                    };

                    $factory->create([
                        'bus_number' => $busNumber,
                        'category' => $category,
                        'type' => $type,
                        'license_plate' => $licensePlate,
                        'is_active' => true,
                        'amenities' => $this->getAmenitiesForCategoryAndType($category, $type),
                        'metadata' => [
                            'year' => fake()->numberBetween(2018, 2024),
                            'manufacturer' => fake()->randomElement(['Volvo', 'Mercedes-Benz', 'Scania', 'Tata', 'Ashok Leyland']),
                            'fuel_type' => fake()->randomElement(['Diesel', 'CNG', 'Electric']),
                            'seating_layout' => $this->getSeatingLayout($category),
                        ],
                    ]);

                    $busCounter++;
                }
            }
        }

        $this->command->info('Created ' . ($busCounter - 1) . ' buses for operator: ' . $operator->name);
    }

    /**
     * Get amenities based on category and type.
     */
    private function getAmenitiesForCategoryAndType(BusCategory $category, BusType $type): array
    {
        $baseAmenities = ['GPS Tracking', 'First Aid Kit'];

        if ($type === BusType::Ac) {
            $baseAmenities[] = 'Air Conditioning';
        }

        $categoryAmenities = match ($category) {
            BusCategory::Economy => ['Reading Lights', 'Mobile Charging Points'],
            BusCategory::Business => ['WiFi', 'Reading Lights', 'Mobile Charging Points', 'Comfortable Seating'],
            BusCategory::Luxury => ['WiFi', 'Entertainment System', 'Reclining Seats', 'Mobile Charging Points', 'Snack Service', 'Blankets'],
            BusCategory::Sleeper => ['Bed Sheets', 'Pillows', 'Privacy Curtains', 'Mobile Charging Points', 'Reading Lights'],
        };

        return array_merge($baseAmenities, $categoryAmenities);
    }

    /**
     * Get license plate prefix based on category and type.
     */
    private function getLicensePlatePrefix(BusCategory $category, BusType $type): string
    {
        $categoryPrefix = match ($category) {
            BusCategory::Economy => 'ECO',
            BusCategory::Business => 'BIZ',
            BusCategory::Luxury => 'LUX',
            BusCategory::Sleeper => 'SLP',
        };

        $typePrefix = match ($type) {
            BusType::Ac => 'AC',
            BusType::NonAc => 'NA',
        };

        return $categoryPrefix . $typePrefix;
    }

    /**
     * Get seating layout based on category.
     */
    private function getSeatingLayout(BusCategory $category): string
    {
        return match ($category) {
            BusCategory::Economy => '2+2',
            BusCategory::Business => '2+2',
            BusCategory::Luxury => '2+1',
            BusCategory::Sleeper => '2+1',
        };
    }
}
