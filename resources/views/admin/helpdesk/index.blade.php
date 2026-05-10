@extends('admin.layout')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-helpdesk.css') }}?v={{ time() }}">
@endpush

@section('title', 'Helpdesk')

@section('content')
<div class="admin-helpdesk">
  <h1 class="h3 mb-3">Helpdesk</h1>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'open' ? 'active' : '' }}" href="{{ route('admin.helpdesk.index', ['tab' => 'open']) }}">
        Belum selesai
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'closed' ? 'active' : '' }}" href="{{ route('admin.helpdesk.index', ['tab' => 'closed']) }}">
        Selesai / ditutup / ditolak
      </a>
    </li>
  </ul>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 admin-helpdesk-table">
          <thead class="table-light">
            <tr>
              <th class="text-center" style="width:3rem;">#</th>
              <th>No. tiket</th>
              <th>Kategori</th>
              <th>Tarikh cipta</th>
              <th class="text-end">Hari diproses</th>
              <th>Status</th>
              <th class="text-end" style="width:5rem;">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($tickets as $t)
              @php
                $days = 0;
                if ($t->created_at) {
                  $days = (int) max(0, $t->created_at->diffInDays(now()));
                }
                $categoryLabel = match ($t->category) {
                  \App\Models\HelpdeskTicket::CATEGORY_TECHNICAL => 'Aduan teknikal',
                  \App\Models\HelpdeskTicket::CATEGORY_SUGGESTION => 'Cadangan penambahbaikan',
                  \App\Models\HelpdeskTicket::CATEGORY_MISCONDUCT => 'Aduan salah laku',
                  default => $t->category,
                };
                $statusLabel = match ($t->status) {
                  \App\Models\HelpdeskTicket::STATUS_OPEN => 'Terbuka',
                  \App\Models\HelpdeskTicket::STATUS_PENDING_REVIEW => 'Menunggu semakan',
                  \App\Models\HelpdeskTicket::STATUS_RESOLVED => 'Selesai',
                  \App\Models\HelpdeskTicket::STATUS_CLOSED => 'Ditutup',
                  \App\Models\HelpdeskTicket::STATUS_REJECTED => 'Ditolak',
                  default => $t->status,
                };
              @endphp
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="fw-semibold">{{ $t->ticket_number }}</td>
                <td>{{ $categoryLabel }}</td>
                <td>{{ $t->created_at?->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }}</td>
                <td class="text-end">{{ $days }}</td>
                <td><span class="badge bg-secondary-subtle text-dark">{{ $statusLabel }}</span></td>
                <td class="text-end">
                  <a href="{{ route('admin.helpdesk.show', $t) }}" class="btn btn-sm btn-outline-primary" title="Lihat">
                    <i class="bi bi-eye"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Tiada rekod.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
