@extends('admin.layout')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-helpdesk.css') }}?v={{ time() }}">
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
@endpush

@php
  use App\Models\HelpdeskTicket;
  use App\Models\HelpdeskTicketReply;
  $tz = 'Asia/Kuala_Lumpur';
  $categoryLabel = match ($ticket->category) {
    HelpdeskTicket::CATEGORY_TECHNICAL => 'Aduan teknikal',
    HelpdeskTicket::CATEGORY_SUGGESTION => 'Cadangan penambahbaikan',
    HelpdeskTicket::CATEGORY_MISCONDUCT => 'Aduan salah laku',
    default => $ticket->category,
  };
  $statusLabel = match ($ticket->status) {
    HelpdeskTicket::STATUS_OPEN => 'Terbuka',
    HelpdeskTicket::STATUS_PENDING_REVIEW => 'Menunggu semakan',
    HelpdeskTicket::STATUS_RESOLVED => 'Selesai',
    HelpdeskTicket::STATUS_CLOSED => 'Ditutup',
    HelpdeskTicket::STATUS_REJECTED => 'Ditolak',
    default => $ticket->status,
  };
  $canRespond = in_array($ticket->status, [HelpdeskTicket::STATUS_OPEN, HelpdeskTicket::STATUS_PENDING_REVIEW], true);
@endphp

@section('title', 'Helpdesk — ' . $ticket->ticket_number)

@section('content')
<div class="admin-helpdesk helpdesk-page--wide">
  @if (session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @if ($errors->has('action'))
    <div class="alert alert-warning py-2">{{ $errors->first('action') }}</div>
  @endif

  <p class="mb-2">
    <a href="{{ route('admin.helpdesk.index', ['tab' => $canRespond ? 'open' : 'closed']) }}" class="btn btn-sm btn-outline-secondary">
      &larr; Kembali ke senarai
    </a>
  </p>

  <h1 class="h4 mb-3">Tiket {{ $ticket->ticket_number }}</h1>

  <div class="helpdesk-summary-card mb-4">
    <div class="row g-2 small">
      <div class="col-sm-3 text-muted">No. tiket</div>
      <div class="col-sm-9 fw-semibold">{{ $ticket->ticket_number }}</div>
      <div class="col-sm-3 text-muted">Kategori</div>
      <div class="col-sm-9">{{ $categoryLabel }}</div>
      <div class="col-sm-3 text-muted">Status</div>
      <div class="col-sm-9"><span class="helpdesk-status-pill">{{ $statusLabel }}</span></div>
      <div class="col-sm-3 text-muted">Pelapor</div>
      <div class="col-sm-9">{{ $ticket->reporter_full_name }} &middot; {{ $ticket->reporter_email }} &middot; {{ $ticket->reporter_phone }}</div>
      @if ($ticket->reportedUser)
        <div class="col-sm-3 text-muted">Ahli dilaporkan</div>
        <div class="col-sm-9">{{ $ticket->reportedUser->name }} ({{ $ticket->reportedUser->email }})</div>
      @endif
    </div>
  </div>

  <h2 class="h5 mb-3 helpdesk-timeline-page-title">Sejarah tiket</h2>

  <div class="helpdesk-timeline mb-4">
    @foreach ($timeline as $item)
      @php
        $isCreate = $item['kind'] === 'create';
        $dateClass = $isCreate ? 'helpdesk-timeline-date--create' : 'helpdesk-timeline-date--event';
        $dotClass = match ($item['kind']) {
          'create' => 'helpdesk-timeline-dot--create',
          HelpdeskTicketReply::KIND_USER_REPLY => 'helpdesk-timeline-dot--reply',
          HelpdeskTicketReply::KIND_STAFF_REPLY => 'helpdesk-timeline-dot--staff',
          HelpdeskTicketReply::KIND_ESCALATION => 'helpdesk-timeline-dot--escalate',
          default => 'helpdesk-timeline-dot--reply',
        };
        $statusLine = match ($item['kind']) {
          'create' => 'STATUS : ' . $item['actor'] . ' telah mencipta tiket.',
          HelpdeskTicketReply::KIND_USER_REPLY => 'STATUS : ' . $item['actor'] . ' telah membalas tiket.',
          HelpdeskTicketReply::KIND_STAFF_REPLY => 'STATUS : ' . $item['actor'] . ' (Sokongan) telah membalas tiket.',
          HelpdeskTicketReply::KIND_ESCALATION => 'STATUS : ' . $item['actor'] . ' telah meningkatkan tiket kepada ' . ($item['escalation_target'] ?? '—') . '.',
          default => $item['kind'],
        };
        $headerClass = match ($item['kind']) {
          HelpdeskTicketReply::KIND_ESCALATION => 'helpdesk-timeline-status--accent',
          HelpdeskTicketReply::KIND_USER_REPLY => 'helpdesk-timeline-status--reply',
          default => 'helpdesk-timeline-status--default',
        };
      @endphp
      <article class="helpdesk-timeline-item">
        <div class="helpdesk-timeline-rail" aria-hidden="true">
          <span class="helpdesk-timeline-dot {{ $dotClass }}"></span>
        </div>
        <div class="helpdesk-timeline-card">
          <div class="helpdesk-timeline-date {{ $dateClass }}">{{ $item['at']->timezone($tz)->format('d/m/Y H:i') }}</div>
          <h3 class="helpdesk-timeline-status {{ $headerClass }}">{{ $statusLine }}</h3>
          <div class="helpdesk-timeline-body ql-snow">
            <div class="ql-editor" style="padding:0;border:none;">{!! $item['body'] !!}</div>
          </div>
          @if ($item['attachment_models']->isNotEmpty())
            <ul class="helpdesk-attach-list mt-2">
              @foreach ($item['attachment_models'] as $att)
                <li>
                  <a href="{{ $attachmentBaseUrl }}/storage/{{ $att->path }}" target="_blank" rel="noopener noreferrer">
                    {{ $att->original_name ?: basename($att->path) }}
                  </a>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </article>
    @endforeach
  </div>

  @if ($canRespond)
    <div class="card shadow-sm">
      <div class="card-header fw-semibold">Tindakan admin</div>
      <div class="card-body">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0 small">
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('admin.helpdesk.respond', $ticket) }}" method="post" enctype="multipart/form-data" id="adminHelpdeskRespondForm">
          @csrf
          <input type="hidden" name="action" id="helpdesk_admin_action" value="reply">

          <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <div id="admin-reply-editor">{!! old('body') !!}</div>
            <textarea name="body" id="admin_reply_body" class="d-none">{{ old('body') }}</textarea>
            @error('body')
              <div class="text-danger small">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Lampiran (PDF / imej, maks. 10 fail)</label>
            <input type="file" class="form-control" name="attachments[]" accept=".pdf,image/*" multiple>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary" data-action="reply">
              <i class="bi bi-reply"></i> Balas
            </button>
            <button type="submit" class="btn btn-success" data-action="resolve">
              <i class="bi bi-check-circle"></i> Selesai (solve)
            </button>
            <button type="submit" class="btn btn-danger" data-action="reject">
              <i class="bi bi-x-circle"></i> Tolak
            </button>
          </div>
          <p class="text-muted small mt-2 mb-0">
            <strong>Balas:</strong> hantar mesej kepada pengguna (status tiket: terbuka).<br>
            <strong>Selesai:</strong> tandakan tiket diselesaikan (status: selesai). Keterangan/lampiran adalah pilihan.<br>
            <strong>Tolak:</strong> tolak tiket (wajib keterangan atau lampiran).
          </p>
        </form>
      </div>
    </div>
  @else
    <div class="alert alert-secondary">Tiket ini telah ditutup / diselesaikan / ditolak. Tiada tindakan lanjut.</div>
  @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
(function () {
  const form = document.getElementById('adminHelpdeskRespondForm');
  if (!form) return;
  const actionInput = document.getElementById('helpdesk_admin_action');
  const bodyField = document.getElementById('admin_reply_body');
  const quill = new Quill('#admin-reply-editor', {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [1, 2, false] }],
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link'],
        ['clean'],
      ],
    },
  });
  if (bodyField.value) {
    quill.root.innerHTML = bodyField.value;
  }
  form.querySelectorAll('button[type="submit"][data-action]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      actionInput.value = btn.getAttribute('data-action') || 'reply';
    });
  });
  form.addEventListener('submit', function () {
    bodyField.value = quill.root.innerHTML;
  });
})();
</script>
@endpush
@endsection
