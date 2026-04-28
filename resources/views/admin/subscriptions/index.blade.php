@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="fw-bold mb-0">Subscriptions</h3>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-2 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Search</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="uuid / user email / package / affiliate code">
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">All</option>
          @foreach(['pending','active','cancelled','expired'] as $s)
            <option value="{{ $s }}" {{ $status===$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.subscriptions.index') }}"><i class="bi bi-arrow-counterclockwise"></i></a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>UUID</th>
            <th>User</th>
            <th>Package</th>
            <th>Status</th>
            <th>Amount</th>
            <th>Period</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($subs as $s)
            <tr>
              <td class="small">{{ $s->uuid }}</td>
              <td>
                <div class="fw-semibold">{{ $s->user?->name ?? '—' }}</div>
                <div class="text-muted small">{{ $s->user?->email ?? '—' }}</div>
              </td>
              <td>
                <div class="fw-semibold">{{ $s->package?->name ?? '—' }}</div>
                <div class="text-muted small">{{ $s->package?->code ?? '—' }}</div>
              </td>
              <td>
                @php
                  $badge = [
                    'active' => 'success',
                    'pending' => 'warning',
                    'cancelled' => 'secondary',
                    'expired' => 'danger',
                  ][$s->status] ?? 'secondary';
                @endphp
                <span class="badge bg-{{ $badge }}">{{ $s->status }}</span>
              </td>
              <td>{{ $s->currency }} {{ $s->amount_myr ? number_format($s->amount_myr * 100, 2) : number_format(0, 2) }}</td>
              <td class="small">
                {{ $s->started_at ? $s->started_at->format('d M Y') : '—' }}
                →
                {{ $s->ends_at ? $s->ends_at->format('d M Y') : '—' }}
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.subscriptions.show', $s->id) }}">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-muted">No subscriptions found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $subs->links() }}</div>
  </div>
</div>
@endsection
