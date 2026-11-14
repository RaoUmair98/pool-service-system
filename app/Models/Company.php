<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A pool service business. Created at signup together with its owner, who becomes its first admin.
 */
class Company extends Model
{
    use HasFactory, LogsActivity;

    /** Currencies for the markets v1 targets (US, UK, Canada, Australia). */
    public const CURRENCIES = [
        'USD' => 'US Dollar (USD)',
        'GBP' => 'British Pound (GBP)',
        'CAD' => 'Canadian Dollar (CAD)',
        'AUD' => 'Australian Dollar (AUD)',
    ];

    /**
     * Common timezones for those markets, grouped for the settings select.
     *
     * @var array<string, array<string, string>>
     */
    public const TIMEZONES = [
        'United States' => [
            'America/New_York' => 'Eastern',
            'America/Chicago' => 'Central',
            'America/Denver' => 'Mountain',
            'America/Phoenix' => 'Arizona',
            'America/Los_Angeles' => 'Pacific',
            'America/Anchorage' => 'Alaska',
            'Pacific/Honolulu' => 'Hawaii',
        ],
        'United Kingdom' => [
            'Europe/London' => 'London',
        ],
        'Canada' => [
            'America/Toronto' => 'Eastern (Toronto)',
            'America/Winnipeg' => 'Central (Winnipeg)',
            'America/Edmonton' => 'Mountain (Edmonton)',
            'America/Vancouver' => 'Pacific (Vancouver)',
        ],
        'Australia' => [
            'Australia/Sydney' => 'Sydney / Melbourne',
            'Australia/Brisbane' => 'Brisbane',
            'Australia/Adelaide' => 'Adelaide',
            'Australia/Perth' => 'Perth',
        ],
        'Other' => [
            'UTC' => 'UTC',
        ],
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'logo_path',
        'currency',
        'timezone',
    ];

    protected $attributes = [
        'currency' => 'USD',
        'timezone' => 'UTC',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function pools()
    {
        return $this->hasMany(Pool::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    /**
     * Flat list of allowed timezone identifiers for validation.
     *
     * @return list<string>
     */
    public static function timezoneIds(): array
    {
        $ids = [];

        foreach (self::TIMEZONES as $zones) {
            $ids = array_merge($ids, array_keys($zones));
        }

        return $ids;
    }

    protected function activityIgnored(): array
    {
        return ['logo_path'];
    }
}
