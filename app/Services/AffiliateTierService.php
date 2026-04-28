<?php

namespace App\Services;

use App\Models\Client;

class AffiliateTierService
{
    public const TIER_NONE = 'none';
    public const TIER_STANDARD = 'standard';
    public const TIER_PRO = 'pro';

    public function tierFor(Client $user): string
    {
        // External affiliate (created via public application) is always PRO
        if (!empty($user->is_external_affiliate)) {
            return self::TIER_PRO;
        }

        $m = strtoupper((string) ($user->status_keahlian ?? 'LITE'));

        if ($m === 'LITE') {
            return self::TIER_NONE;
        }
        if ($m === 'HYPE') {
            return self::TIER_PRO;
        }
        if ($m === 'ACTIVE' || $m === 'GRADUATE') {
            return !empty($user->affiliate_pro_approved_at) ? self::TIER_PRO : self::TIER_STANDARD;
        }

        return self::TIER_NONE;
    }
}

