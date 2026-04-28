@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-0">Affiliate Code Detail</h3>
    <div class="text-muted small">{{ $code->code }}</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('admin.affiliate.codes') }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6 mb-2"><span class="text-muted">Code:</span> <span class="fw-semibold">{{ $code->code }}</span></div>
      <div class="col-md-6 mb-2"><span class="text-muted">Clicks:</span> {{ number_format($code->clicks) }}</div>
      <div class="col-md-6 mb-2"><span class="text-muted">Owner:</span> {{ $code->user?->name ?? '—' }}</div>
      <div class="col-md-6 mb-2"><span class="text-muted">Email:</span> {{ $code->user?->email ?? '—' }}</div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="mb-3">Recent Commissions (as Referrer)</h5>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Referred User</th>
            <th>Subscription</th>
            <th>Percent</th>
            <th>Commission</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentComms as $c)
            <tr>
              <td>
                <div class="fw-semibold">{{ $c->referred?->name ?? '—' }}</div>
                <div class="text-muted small">{{ $c->referred?->email ?? '—' }}</div>
              </td>
              <td class="small">{{ $c->subscription?->uuid ?? '—' }}</td>
              <td>{{ $c->commission_percent }}%</td>
              <td>MYR {{ $c->commission_myr }}</td>
              <td>{{ $c->created_at ? $c->created_at->format('d M Y, H:i') : '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-muted">No commissions.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
