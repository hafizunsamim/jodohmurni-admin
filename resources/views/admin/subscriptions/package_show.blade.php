@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-0">Package Detail</h3>
    <div class="text-muted small">{{ $package->code }}</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('admin.packages.index') }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6 mb-2"><span class="text-muted">Name:</span> <span class="fw-semibold">{{ $package->name }}</span></div>
      <div class="col-md-6 mb-2"><span class="text-muted">Price:</span> <span class="fw-semibold">{{ $package->currency }} {{ $package->price_myr }}</span></div>
      <div class="col-md-6 mb-2"><span class="text-muted">Duration:</span> {{ $package->duration_days ? $package->duration_days.' days' : '—' }}</div>
      <div class="col-md-6 mb-2"><span class="text-muted">Affiliate %:</span> {{ $package->affiliate_percent }}%</div>
      <div class="col-md-6 mb-2"><span class="text-muted">Active:</span> {{ $package->is_active ? 'Yes' : 'No' }}</div>
      <div class="col-md-6 mb-2"><span class="text-muted">Ebook:</span> {{ $package->ebook_path ?? '—' }}</div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="mb-3">Recent Subscriptions</h5>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>UUID</th>
            <th>User</th>
            <th>Status</th>
            <th>Amount</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentSubs as $s)
            <tr>
              <td class="small">{{ $s->uuid }}</td>
              <td>
                <div class="fw-semibold">{{ $s->user?->name ?? '—' }}</div>
                <div class="text-muted small">{{ $s->user?->email ?? '—' }}</div>
              </td>
              <td><span class="badge bg-secondary">{{ $s->status }}</span></td>
              <td>{{ $s->currency }} {{ $s->amount_myr }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.subscriptions.show', $s->id) }}">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-muted">No subscriptions for this package.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
