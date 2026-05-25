<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SettingsSeedCommand extends Command
{
    protected $signature = 'settings:seed';

    protected $description = 'Seed e-commerce settings to database';

    public function handle(): int
    {
        try {
            $settings = config('settings');

            foreach ($settings as $group => $groupSettings) {
                foreach ($groupSettings as $key => $config) {
                    $settingKey = $group . '.' . $key;
                    $defaultValue = $config['default'] ?? null;
                    $description = $config['description'] ?? '';

                    try {
                        // Using Spatie LaravelSettings helper function
                        \settings($settingKey, $defaultValue);

                        $this->info("✓ Seeded: {$settingKey}");
                    } catch (\Exception $e) {
                        $this->warn("⚠ Failed to seed {$settingKey}: " . $e->getMessage());
                    }
                }
            }

            $this->info("\n✓ Settings seeded successfully!");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error seeding settings: " . $e->getMessage());

            return self::FAILURE;
        }
    }
}
