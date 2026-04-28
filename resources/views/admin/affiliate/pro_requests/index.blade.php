@extends('admin.layout')

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="fw-bold mb-0">Affiliate Pro Requests</h3>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.affiliate.pro_requests.index') }}" class="row g-2">
      <div class="col-12 col-md-5">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="pending" @selected($status==='pending')>Pending</option>
          <option value="approved" @selected($status==='approved')>Approved</option>
          <option value="rejected" @selected($status==='rejected')>Rejected</option>
          <option value="all" @selected($status==='all')>All</option>
        </select>
      </div>
      <div class="col-12 col-md-5">
        <label class="form-label">Search user</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="name / email">
      </div>
      <div class="col-12 col-md-2 d-flex gap-2 flex-wrap">
        <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search"></i> Filter</button>
        <a class="btn btn-outline-secondary flex-shrink-0" href="{{ route('admin.affiliate.pro_requests.index') }}" aria-label="Reset filter">
          <i class="bi bi-arrow-counterclockwise"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table id="pro-requests-table" class="table align-middle w-100">
        <thead>
          <tr>
            <th>User</th>
            <th>Membership</th>
            <th>Tier</th>
            <th>Status</th>
            <th>Requested</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
            @php
              $st = strtolower((string) $r->status);
              $type = $r->request_type ?? 'registered';
            @endphp
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div class="fw-semibold">
                    @if($type === 'external')
                      {{ $r->full_name ?? '—' }}
                    @else
                      {{ $r->user?->name ?? '—' }}
                    @endif
                  </div>
                  @if($type === 'external')
                    <span class="badge text-bg-info">External Applicant</span>
                  @else
                    <span class="badge text-bg-secondary">Registered User</span>
                  @endif
                </div>
                <div class="text-muted small">
                  @if($type === 'external')
                    {{ $r->email ?? '—' }}
                  @else
                    {{ $r->user?->email ?? '—' }}
                  @endif
                </div>
              </td>
              <td>
                @if($type === 'external')
                  —
                @else
                  {{ $r->user?->status_keahlian ?? '—' }}
                @endif
              </td>
              <td>
                @if($type === 'external')
                  pro
                @else
                  {{ $r->current_affiliate_tier ?? '—' }}
                @endif
              </td>
              <td>
                @if($st==='approved')
                  <span class="badge text-bg-success">Approved</span>
                @elseif($st==='rejected')
                  <span class="badge text-bg-danger">Rejected</span>
                @else
                  <span class="badge text-bg-warning">Pending</span>
                @endif
              </td>
              <td class="small">{{ $r->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
              <td class="text-end">
                @php
                  $modalTarget = ($type === 'external')
                    ? ('#proReqModalexternal' . $r->id)
                    : ('#proReqModal' . $r->id);
                @endphp
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="{{ $modalTarget }}">
                  View
                </button>
              </td>
            </tr>

            @push('modals')
              @if($type === 'external')
                @include('admin.affiliate.pro_requests._modal_external', ['r' => $r])
              @else
                @include('admin.affiliate.pro_requests._modal', ['r' => $r])
              @endif
            @endpush
          @empty
            {{-- Biarkan kosong. DataTables akan paparkan emptyTable message. --}}
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
(function () {
  function syncRequired(form) {
    var reject = form.querySelector('.decision-reject');
    var approve = form.querySelector('.decision-approve');
    var feedback = form.querySelector('.admin-feedback');
    if (!feedback) return;
    var isReject = reject && reject.checked;
    feedback.required = !!isReject;
    if (isReject) {
      feedback.setAttribute('minlength', '5');
    } else {
      feedback.removeAttribute('minlength');
    }
  }

  document.querySelectorAll('.affProReviewForm').forEach(function (form) {
    form.addEventListener('change', function () {
      syncRequired(form);
    });
    syncRequired(form);
  });

  // DataTables Responsive: keep table on mobile, with search + pagination.
  if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
    jQuery('#pro-requests-table').DataTable({
      paging: true,
      searching: true,
      info: false,
      lengthChange: false,
      ordering: false,
      responsive: false,
      scrollX: true,
      autoWidth: false,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      language: {
        search: '',
        searchPlaceholder: 'Search table...',
        emptyTable: 'No requests found.',
        zeroRecords: 'No requests found.'
      }
    });
  }
})();
</script>
@endpush

