@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="fw-bold mb-0">Subscription Packages</h3>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.packages.index') }}" class="row g-2 align-items-end">
      <div class="col-md-7">
        <label class="form-label">Search</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="code / name">
      </div>
      <div class="col-md-3">
        <label class="form-label">Active</label>
        <select class="form-select" name="active">
          <option value="">All</option>
          <option value="1" {{ $active==='1' ? 'selected' : '' }}>Active</option>
          <option value="0" {{ $active==='0' ? 'selected' : '' }}>Inactive</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.packages.index') }}"><i class="bi bi-arrow-counterclockwise"></i></a>
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
            <th>Code</th>
            <th>Name</th>
            <th>Price</th>
            <th>Duration</th>
            <th>Affiliate %</th>
            <th>Active</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($packages as $p)
            <tr>
              <td class="fw-semibold">{{ $p->code }}</td>
              <td>{{ $p->name }}</td>
              <td>{{ $p->currency }} {{ $p->price_myr }}</td>
              <td>{{ $p->duration_days ? $p->duration_days.' days' : '—' }}</td>
              <td>{{ $p->affiliate_percent }}%</td>
              <td>
                <span class="badge bg-{{ $p->is_active ? 'success' : 'secondary' }}">
                  {{ $p->is_active ? 'active' : 'inactive' }}
                </span>
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.packages.show', $p->id) }}">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-muted">No packages found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $packages->links() }}</div>
  </div>
</div>
@endsection
