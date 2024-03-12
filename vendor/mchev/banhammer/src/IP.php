<?php

namespace Mchev\Banhammer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Mchev\Banhammer\Models\Ban;

class IP
{
    public static function ban(string|array $ips, array $metas = [], string $date = null): void
    {
        $bannedIps = self::getBannedIPsFromCache();

        foreach ((array) $ips as $ip) {
            if (! in_array($ip, $bannedIps)) {
                Ban::create([
                    'ip' => $ip,
                    'metas' => count($metas) ? $metas : null,
                    'expired_at' => $date,
                ]);
            }
        }
    }

    public static function unban(string|array $ips): void
    {
        $ips = (array) $ips;
        Ban::whereIn('ip', $ips)->delete();
        Cache::put('banned-ips', self::banned()->pluck('ip')->toArray());
    }

    public static function isBanned(string $ip): bool
    {
        return Ban::where('ip', $ip)
            ->notExpired()
            ->exists();
    }

    public static function banned(): Builder
    {
        return Ban::whereNotNull('ip')
            ->with('createdBy')
            ->notExpired();
    }

    public static function getBannedIPsFromCache(): array
    {
        return Cache::has('banned-ips')
            ? Cache::get('banned-ips')
            : self::banned()->pluck('ip')->unique()->toArray();
    }
}
