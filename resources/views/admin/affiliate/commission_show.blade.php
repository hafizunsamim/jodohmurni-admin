@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-0">Commission Detail</h3>
    <div class="text-muted small">#{{ $commission->id }}</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('admin.affiliate.commissions') }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">Commission</h6>
        <div class="mb-2"><span class="text-muted">Code Used:</span> <span class="fw-semibold">{{ $commission->affiliate_code_used }}</span></div>
        <div class="mb-2"><span class="text-muted">Amount:</span> MYR {{ $commission->commission_myr }}</div>
        <div class="mb-2">
          <span class="text-muted">Status:</span>
          @php($st = strtolower((string)($commission->status ?? 'pending')))
          @if($st === 'paid')
            <span class="badge text-bg-success">Paid</span>
          @elseif($st === 'rejected')
            <span class="badge text-bg-danger">Rejected</span>
          @else
            <span class="badge text-bg-warning">Pending</span>
          @endif
        </div>
        <div class="mb-0"><span class="text-muted">Created:</span> {{ $commission->created_at ? $commission->created_at->format('d M Y, H:i') : '—' }}</div>

        <hr>

        <h6 class="text-muted mb-2">Update Status</h6>
        <form method="POST" action="{{ route('admin.affiliate.commissions.status', $commission->id) }}" class="d-flex gap-2 flex-wrap align-items-center">
          @csrf
          <select name="status" class="form-select" style="max-width: 220px;">
            @php($current = strtolower((string)($commission->status ?? 'pending')))
            <option value="pending" @selected($current === 'pending')>Pending</option>
            <option value="paid" @selected($current === 'paid')>Paid</option>
            <option value="rejected" @selected($current === 'rejected')>Rejected</option>
          </select>
          <button type="submit" class="btn btn-primary">Save</button>
        </form>
        <div class="text-muted small mt-2">
          Pending = belum dibayar. Paid = bayaran selesai. Rejected = ditolak (tiada bayaran).
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">People</h6>
        <div class="mb-2"><span class="text-muted">Referrer:</span> <span class="fw-semibold">{{ $commission->referrer?->name ?? '—' }}</span></div>
        <div class="mb-2"><span class="text-muted">Referrer Email:</span> {{ $commission->referrer?->email ?? '—' }}</div>
        <hr>
        <div class="mb-2"><span class="text-muted">Referred:</span> <span class="fw-semibold">{{ $commission->referred?->name ?? '—' }}</span></div>
        <div class="mb-0"><span class="text-muted">Referred Email:</span> {{ $commission->referred?->email ?? '—' }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">Subscription</h6>
        <div class="mb-2"><span class="text-muted">Subscription UUID:</span> {{ $commission->subscription?->uuid ?? '—' }}</div>
        <div class="mb-2"><span class="text-muted">Status:</span> {{ $commission->subscription?->status ?? '—' }}</div>
        <div class="mb-0">
          <a class="btn btn-sm btn-outline-primary"
             href="{{ $commission->subscription ? route('admin.subscriptions.show', $commission->subscription->id) : '#' }}"
             @if(!$commission->subscription) disabled @endif
          >
            View Subscription
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
