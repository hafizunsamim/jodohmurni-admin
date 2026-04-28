<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateProRequest extends Model
{
    protected $table = 'affiliate_pro_requests';

    protected $fillable = [
        'user_id',
        'reason',
        'promotion_platform',
        'status',
        'reviewed_at',
        'reviewed_by_admin_id',
        'admin_feedback',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(Client::class, 'user_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by_admin_id', 'id');
    }
}

