@php
  $modalId = 'proReqModalexternal' . $r->id;
  $st = strtolower((string) $r->status);
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-mobile-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <div class="fw-bold">External Affiliate Application</div>
          <div class="text-muted small">#{{ $r->id }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
              <div class="card-body">
                <h6 class="text-muted mb-3">Applicant</h6>
                <div class="mb-2"><span class="text-muted">Name:</span> <span class="fw-semibold">{{ $r->full_name }}</span></div>
                <div class="mb-2"><span class="text-muted">Email:</span> {{ $r->email }}</div>
                <div class="mb-0"><span class="text-muted">Phone:</span> {{ $r->phone_number }}</div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
              <div class="card-body">
                <h6 class="text-muted mb-3">Request</h6>
                <div class="mb-2">
                  <span class="text-muted">Type:</span>
                  <span class="badge text-bg-info">External Applicant</span>
                </div>
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
                <div class="mb-2"><span class="text-muted">Requested:</span> {{ $r->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div class="mb-2"><span class="text-muted">Platform promosi:</span> {{ $r->promotion_platform ?? '—' }}</div>
                <div class="mb-2"><span class="text-muted">Reason:</span><div class="mt-1">{{ $r->reason }}</div></div>
                @if($r->reviewed_at)
                  <div class="mb-2"><span class="text-muted">Reviewed:</span> {{ $r->reviewed_at->format('d/m/Y H:i') }}</div>
                @endif
                @if($r->admin_feedback)
                  <div class="mb-0"><span class="text-muted">Admin feedback:</span><div class="mt-1">{{ $r->admin_feedback }}</div></div>
                @endif
              </div>
            </div>
          </div>

          @if($st === 'pending')
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="fw-bold text-uppercase mb-3">Kelulusan External Affiliate</div>

                <form method="POST" action="{{ route('admin.affiliate.pro_requests.external.review', $r->id) }}" class="affProReviewForm">
                  @csrf
                  <div class="p-3 rounded-3" style="background:#f8f9fa;">
                    <div class="row g-3 align-items-start">
                      <div class="col-12 col-lg-3">
                        <div class="text-uppercase fw-semibold small">Pengesahan <span class="text-danger">*</span></div>
                      </div>
                      <div class="col-12 col-lg-9">
                        <div class="d-flex gap-4 flex-wrap">
                          <div class="form-check">
                            <input class="form-check-input decision-approve" type="radio" name="decision" value="approved">
                            <label class="form-check-label">Lulus</label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input decision-reject" type="radio" name="decision" value="rejected">
                            <label class="form-check-label">Tolak</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-lg-3">
                        <div class="text-uppercase fw-semibold small">Ulasan</div>
                      </div>
                      <div class="col-12 col-lg-9">
                        <textarea class="form-control admin-feedback" name="admin_feedback" rows="6" maxlength="2000"
                                  style="min-height: 160px;"></textarea>
                        <div class="text-muted small mt-1">Wajib jika pilih <strong>Tolak</strong>.</div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-3 d-flex justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          @endif
        </div>

      </div>
    </div>
  </div>
</div>

