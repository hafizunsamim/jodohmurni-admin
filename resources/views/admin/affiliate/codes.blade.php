@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="fw-bold mb-0">Affiliate Codes</h3>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.affiliate.codes') }}" class="row g-2 align-items-end">
      <div class="col-md-9">
        <label class="form-label">Search</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="code / user name / user email">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.affiliate.codes') }}"><i class="bi bi-arrow-counterclockwise"></i></a>
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
            <th>User</th>
            <th>Clicks</th>
            <th>Created</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($codes as $c)
            <tr>
              <td class="fw-semibold">{{ $c->code }}</td>
              <td>
                <div class="fw-semibold">{{ $c->user?->name ?? '—' }}</div>
                <div class="text-muted small">{{ $c->user?->email ?? '—' }}</div>
              </td>
              <td>{{ number_format($c->clicks) }}</td>
              <td>{{ $c->created_at ? $c->created_at->format('d M Y') : '—' }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.affiliate.codes.show', $c->id) }}">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-muted">No affiliate codes found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $codes->links() }}</div>
  </div>
</div>
@endsection
