<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalAffiliateApplication extends Model
{
    protected $table = 'external_affiliate_applications';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'reason',
        'promotion_platform',
        'status',
        'reviewed_at',
        'reviewed_by_admin_id',
        'admin_feedback',
        'user_id',
        'approved_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

