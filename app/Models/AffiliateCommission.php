<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $table = 'affiliate_commissions';

    public $timestamps = false; // table only has created_at (no updated_at)
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'referrer_user_id','referred_user_id','subscription_id',
        'affiliate_code_used','commission_percent','commission_sen','status',
    ];

    protected $casts = [
        'commission_percent' => 'integer',
        'commission_sen' => 'integer',
        'status' => 'string',
        'created_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';

    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PAID,
            self::STATUS_REJECTED,
        ];
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function referrer()
    {
        return $this->belongsTo(Client::class, 'referrer_user_id', 'id');
    }

    public function referred()
    {
        return $this->belongsTo(Client::class, 'referred_user_id', 'id');
    }

    public function getCommissionMyrAttribute(): string
    {
        $sen = (int) ($this->commission_sen ?? 0);
        return number_format($sen / 100, 2);
    }
}
