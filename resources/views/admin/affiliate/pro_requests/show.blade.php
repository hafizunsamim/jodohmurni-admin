@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-0">Affiliate Pro Request</h3>
    <div class="text-muted small">#{{ $req->id }}</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('admin.affiliate.pro_requests.index') }}">
    <i class="bi bi-arrow-left"></i> Back
  </a>
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

@php($st = strtolower((string)$req->status))

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">User</h6>
        <div class="mb-2"><span class="text-muted">Name:</span> <span class="fw-semibold">{{ $req->user?->name ?? '—' }}</span></div>
        <div class="mb-2"><span class="text-muted">Email:</span> {{ $req->user?->email ?? '—' }}</div>
        <div class="mb-2"><span class="text-muted">Membership:</span> {{ $req->user?->status_keahlian ?? '—' }}</div>
        <div class="mb-0"><span class="text-muted">Current Tier:</span> {{ $tier ?? '—' }}</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted mb-3">Request</h6>
        <div class="mb-2">
          <span class="text-muted">Status:</span>
          @if($st==='approved')
            <span class="badge text-bg-success">Approved</span>
          @elseif($st==='rejected')
            <span class="badge text-bg-danger">Rejected</span>
          @else
            <span class="badge text-bg-warning">Pending</span>
          @endif
        </div>
        <div class="mb-2"><span class="text-muted">Requested:</span> {{ $req->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
        <div class="mb-2"><span class="text-muted">Reason:</span><div class="mt-1">{{ $req->reason }}</div></div>
        @if($req->reviewed_at)
          <div class="mb-2"><span class="text-muted">Reviewed:</span> {{ $req->reviewed_at->format('d/m/Y H:i') }}</div>
        @endif
        @if($req->reviewer)
          <div class="mb-2"><span class="text-muted">Reviewer:</span> {{ $req->reviewer->name }} ({{ $req->reviewer->email }})</div>
        @endif
        @if($req->admin_feedback)
          <div class="mb-0"><span class="text-muted">Admin feedback:</span><div class="mt-1">{{ $req->admin_feedback }}</div></div>
        @endif
      </div>
    </div>
  </div>

  @if($st==='pending')
  <div class="col-lg-12">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="fw-bold text-uppercase">Kelulusan Affiliate Pro</div>
        </div>

        <form method="POST" action="{{ route('admin.affiliate.pro_requests.review', $req->id) }}" id="affProReviewForm">
          @csrf

          <div class="p-3 rounded-3" style="background:#f8f9fa;">
            <div class="row g-3 align-items-start">
              <div class="col-lg-3">
                <div class="text-uppercase fw-semibold small">Pengesahan <span class="text-danger">*</span></div>
              </div>
              <div class="col-lg-9">
                <div class="d-flex gap-4">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="decision" id="decApprove" value="approved" @checked(old('decision')==='approved')>
                    <label class="form-check-label" for="decApprove">Lulus</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="decision" id="decReject" value="rejected" @checked(old('decision')==='rejected')>
                    <label class="form-check-label" for="decReject">Tolak</label>
                  </div>
                </div>
                @error('decision')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-lg-3">
                <div class="text-uppercase fw-semibold small">Ulasan</div>
              </div>
              <div class="col-lg-9">
                <textarea class="form-control" name="admin_feedback" id="adminFeedback" rows="4" maxlength="2000"
                          placeholder="">{{ old('admin_feedback') }}</textarea>
                @error('admin_feedback')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="mt-3 d-flex justify-content-between gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('admin.affiliate.pro_requests.index') }}">Kembali</a>
            <button class="btn btn-primary" type="submit">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
  var reject = document.getElementById('decReject');
  var approve = document.getElementById('decApprove');
  var feedback = document.getElementById('adminFeedback');

  function syncRequired() {
    if (!feedback) return;
    var isReject = reject && reject.checked;
    feedback.required = !!isReject;
    if (isReject) {
      feedback.setAttribute('minlength', '5');
    } else {
      feedback.removeAttribute('minlength');
    }
  }

  if (reject) reject.addEventListener('change', syncRequired);
  if (approve) approve.addEventListener('change', syncRequired);
  syncRequired();
})();
</script>
@endpush

