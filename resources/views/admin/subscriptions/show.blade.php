@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-0">Subscription Detail</h3>
    <div class="text-muted small">{{ $sub->uuid }}</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('admin.subscriptions.index') }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">Subscription</h6>
        <div class="mb-2"><span class="text-muted">Status:</span> <span class="fw-semibold">{{ $sub->status }}</span></div>
        <div class="mb-2"><span class="text-muted">Amount:</span> <span class="fw-semibold">{{ $sub->currency }} {{ $sub->amount_myr }}</span></div>
        <div class="mb-2"><span class="text-muted">Started:</span> {{ $sub->started_at?->format('d M Y, H:i') ?? '—' }}</div>
        <div class="mb-2"><span class="text-muted">Ends:</span> {{ $sub->ends_at?->format('d M Y, H:i') ?? '—' }}</div>
        <div class="mb-0"><span class="text-muted">Affiliate Code Used:</span> {{ $sub->affiliate_code_used ?? '—' }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">User & Package</h6>
        <div class="mb-2"><span class="text-muted">User:</span> <span class="fw-semibold">{{ $sub->user?->name ?? '—' }}</span></div>
        <div class="mb-2"><span class="text-muted">Email:</span> {{ $sub->user?->email ?? '—' }}</div>
        <hr>
        <div class="mb-2"><span class="text-muted">Package:</span> <span class="fw-semibold">{{ $sub->package?->name ?? '—' }}</span></div>
        <div class="mb-0"><span class="text-muted">Package Code:</span> {{ $sub->package?->code ?? '—' }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">Referrer</h6>
        <div class="mb-2"><span class="text-muted">Referrer User:</span> {{ $sub->referrer?->name ?? '—' }}</div>
        <div class="mb-0"><span class="text-muted">Referrer Email:</span> {{ $sub->referrer?->email ?? '—' }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">Commission</h6>
        @if($sub->commission)
          <div class="mb-2"><span class="text-muted">Percent:</span> {{ $sub->commission->commission_percent }}%</div>
          <div class="mb-2"><span class="text-muted">Amount:</span> MYR {{ number_format(($sub->commission->commission_sen ?? 0)/100, 2) }}</div>
          <div class="mb-0"><span class="text-muted">Created:</span> {{ $sub->commission->created_at?->format('d M Y, H:i') ?? '—' }}</div>
        @else
          <div class="text-muted">No commission record for this subscription.</div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
