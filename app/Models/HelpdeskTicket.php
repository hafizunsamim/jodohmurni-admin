<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpdeskTicket extends Model
{
    public const CATEGORY_TECHNICAL = 'technical';

    public const CATEGORY_SUGGESTION = 'suggestion';

    public const CATEGORY_MISCONDUCT = 'misconduct';

    public const STATUS_OPEN = 'open';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'ticket_number',
        'category',
        'reporter_full_name',
        'reporter_email',
        'reporter_phone',
        'description',
        'reported_user_id',
        'status',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(HelpdeskTicketAttachment::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(HelpdeskTicketReply::class, 'helpdesk_ticket_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'reported_user_id');
    }

    public function scopeUnsolved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_OPEN,
            self::STATUS_PENDING_REVIEW,
        ]);
    }

    public function scopeSolvedOrClosed($query)
    {
        return $query->whereIn('status', [
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
            self::STATUS_REJECTED,
        ]);
    }
}
