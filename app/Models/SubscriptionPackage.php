<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $table = 'subscription_packages';

    protected $fillable = [
        'code','name','price_sen','currency','duration_days',
        'affiliate_percent','ebook_path','is_active','sort_order',
    ];

    protected $casts = [
        'price_sen' => 'integer',
        'duration_days' => 'integer',
        'affiliate_percent' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'package_id');
    }

    public function getPriceMyrAttribute(): string
    {
        $sen = (int) ($this->price_sen ?? 0);
        return number_format($sen / 100, 2);
    }
}
