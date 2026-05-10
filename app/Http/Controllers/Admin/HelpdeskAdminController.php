<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\HelpdeskTicketAdminActionMail;
use App\Models\HelpdeskTicket;
use App\Models\HelpdeskTicketReply;
use App\Models\HelpdeskTicketReplyAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class HelpdeskAdminController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'open');
        if (! in_array($tab, ['open', 'closed'], true)) {
            $tab = 'open';
        }

        if ($tab === 'open') {
            $tickets = HelpdeskTicket::query()
                ->unsolved()
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $tickets = HelpdeskTicket::query()
                ->solvedOrClosed()
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('admin.helpdesk.index', compact('tickets', 'tab'));
    }

    public function show(HelpdeskTicket $helpdeskTicket)
    {
        $ticket = $helpdeskTicket->load(['attachments', 'replies.attachments', 'reportedUser']);

        $timeline = $this->buildTimeline($ticket);

        $memberStorageBase = rtrim(config('services.member_app.url', config('app.url')), '/');

        return view('admin.helpdesk.show', [
            'ticket' => $ticket,
            'timeline' => $timeline,
            'memberStorageBase' => $memberStorageBase,
        ]);
    }

    public function respond(Request $request, HelpdeskTicket $helpdeskTicket)
    {
        $ticket = $helpdeskTicket;
        if (! in_array($ticket->status, [HelpdeskTicket::STATUS_OPEN, HelpdeskTicket::STATUS_PENDING_REVIEW], true)) {
            return redirect()
                ->route('admin.helpdesk.show', $ticket)
                ->withErrors(['action' => 'Tiket ini tidak lagi aktif untuk balasan atau tindakan.']);
        }

        $validated = $request->validate([
            'action' => ['required', Rule::in(['reply', 'resolve', 'reject'])],
            'body' => ['nullable', 'string', 'max:65535'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:pdf,jpeg,jpg,png,gif,webp', 'max:10240'],
        ]);

        $uploadedFiles = $request->file('attachments', []);
        if (! is_array($uploadedFiles)) {
            $uploadedFiles = [];
        }

        $bodyHtml = $validated['body'] ?? '';
        $plain = trim(html_entity_decode(strip_tags($bodyHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $hasFiles = false;
        foreach ($uploadedFiles as $file) {
            if ($file && $file->isValid()) {
                $hasFiles = true;
                break;
            }
        }

        if ($validated['action'] === 'reject' && $plain === '' && ! $hasFiles) {
            return back()
                ->withInput()
                ->withErrors([
                    'body' => 'Sila nyatakan sebab penolakan (keterangan atau lampiran).',
                ]);
        }
        if ($validated['action'] === 'reply' && $plain === '' && ! $hasFiles) {
            return back()
                ->withInput()
                ->withErrors([
                    'body' => 'Sila isi keterangan atau lampiran untuk balasan.',
                ]);
        }

        $adminName = auth('admin')->user()->name ?? 'Admin';

        DB::transaction(function () use ($ticket, $validated, $uploadedFiles, $plain, $hasFiles, $bodyHtml, $adminName) {
            if ($plain !== '' || $hasFiles) {
                $reply = HelpdeskTicketReply::create([
                    'helpdesk_ticket_id' => $ticket->id,
                    'event_kind' => HelpdeskTicketReply::KIND_STAFF_REPLY,
                    'actor_display_name' => $adminName,
                    'body' => $bodyHtml,
                ]);

                foreach ($uploadedFiles as $file) {
                    if (! $file || ! $file->isValid()) {
                        continue;
                    }
                    $path = $file->store('helpdesk-attachments', 'public');
                    HelpdeskTicketReplyAttachment::create([
                        'helpdesk_ticket_reply_id' => $reply->id,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            $newStatus = match ($validated['action']) {
                'reply' => HelpdeskTicket::STATUS_OPEN,
                'resolve' => HelpdeskTicket::STATUS_RESOLVED,
                'reject' => HelpdeskTicket::STATUS_REJECTED,
            };

            $ticket->update(['status' => $newStatus]);
        });

        $ticket->refresh();

        try {
            Mail::send(new HelpdeskTicketAdminActionMail($ticket, $validated['action']));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('admin.helpdesk.show', $helpdeskTicket)
            ->with('success', 'Tindakan helpdesk telah disimpan.');
    }

    /**
     * @return list<array{kind: string, at: \Carbon\Carbon, actor: string, body: string, attachment_models: \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>, escalation_target: ?string}>
     */
    private function buildTimeline(HelpdeskTicket $ticket): array
    {
        $items = [];

        $items[] = [
            'kind' => 'create',
            'at' => $ticket->created_at,
            'actor' => $ticket->reporter_full_name,
            'body' => $ticket->description,
            'attachment_models' => $ticket->attachments,
            'escalation_target' => null,
        ];

        foreach ($ticket->replies->sortBy('created_at') as $reply) {
            $items[] = [
                'kind' => $reply->event_kind,
                'at' => $reply->created_at,
                'actor' => $reply->actor_display_name,
                'body' => $reply->body,
                'attachment_models' => $reply->attachments,
                'escalation_target' => $reply->escalation_target_name,
            ];
        }

        usort($items, function ($a, $b) {
            return $b['at']->timestamp <=> $a['at']->timestamp;
        });

        return $items;
    }
}
