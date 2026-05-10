<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpdeskTicketReply extends Model
{
    protected $table = 'helpdesk_ticket_replies';

    public const KIND_USER_REPLY = 'user_reply';

    public const KIND_STAFF_REPLY = 'staff_reply';

    public const KIND_ESCALATION = 'escalation';

    protected $fillable = [
        'helpdesk_ticket_id',
        'event_kind',
        'actor_display_name',
        'body',
        'escalation_target_name',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTicket::class, 'helpdesk_ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(HelpdeskTicketReplyAttachment::class, 'helpdesk_ticket_reply_id');
    }
}
