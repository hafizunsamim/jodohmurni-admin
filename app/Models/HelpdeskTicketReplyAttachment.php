<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpdeskTicketReplyAttachment extends Model
{
    protected $table = 'helpdesk_ticket_reply_attachments';

    protected $fillable = [
        'helpdesk_ticket_reply_id',
        'path',
        'original_name',
    ];

    public function reply(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTicketReply::class, 'helpdesk_ticket_reply_id');
    }
}
