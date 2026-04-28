@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h3 class="fw-bold mb-0">Affiliate Commissions</h3>
</div>

@php
  $totalPendingMyr = $totalPendingMyr ?? '0.00';
  $totalReferrers = $totalReferrers ?? 0;
  $totalTransactions = $totalTransactions ?? 0;

  $groups = $commissions->getCollection()
    ->groupBy(function ($c) {
      return (string) ($c->referrer_user_id ?? '0');
    });
@endphp

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="text-muted fw-semibold">Total Pending Payout</div>
        </div>
        <div class="mt-1 fs-4 fw-bold">MYR {{ $totalPendingMyr }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted fw-semibold">Total Referrers</div>
        <div class="mt-1 fs-4 fw-bold">{{ number_format($totalReferrers) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted fw-semibold">Total Transactions</div>
        <div class="mt-1 fs-4 fw-bold">{{ number_format($totalTransactions) }}</div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.affiliate.commissions') }}" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Search</label>
        <input class="form-control" name="q" value="{{ $q }}" placeholder="affiliate code / referrer / referred / subscription uuid">
      </div>
      <div class="col-md-2">
        <label class="form-label">Min Commission (sen)</label>
        <input class="form-control" name="min_sen" value="{{ $minSen }}" placeholder="e.g. 500">
      </div>
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          @php
            $s = strtolower((string) request('status', 'all'));
          @endphp
          <option value="all" {{ $s === 'all' ? 'selected' : '' }}>All</option>
          <option value="pending" {{ $s === 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="paid" {{ $s === 'paid' ? 'selected' : '' }}>Paid</option>
          <option value="rejected" {{ $s === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Date From</label>
        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
      </div>
      <div class="col-md-2 d-flex gap-2">
        <div class="w-100">
          <label class="form-label">Date To</label>
          <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
        </div>
      </div>
      <div class="col-md-12 d-flex gap-2 justify-content-end mt-2">
        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Filter</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.affiliate.commissions') }}"><i class="bi bi-arrow-counterclockwise"></i></a>
      </div>
    </form>
  </div>
</div>

<form id="commissionBulkForm" method="POST" action="{{ route('admin.affiliate.commissions.bulk.status') }}">
  @csrf
  <input type="hidden" name="status" id="commissionBulkStatus" value="paid">

  @forelse($groups as $referrerKey => $items)
    @php
      $referrer = optional($items->first())->referrer;
      $refName = $referrer?->name ?? '—';
      $refEmail = $referrer?->email ?? '—';
      $totalSen = (int) $items->sum('commission_sen');
      $totalMyr = number_format($totalSen / 100, 2);
      $txCount = (int) $items->count();
      $pendingCount = (int) $items->filter(function ($c) {
        return strtolower((string) ($c->status ?? 'pending')) === 'pending';
      })->count();
      $paidCount = (int) $items->filter(function ($c) {
        return strtolower((string) ($c->status ?? 'pending')) === 'paid';
      })->count();
      $rejectedCount = (int) $items->filter(function ($c) {
        return strtolower((string) ($c->status ?? 'pending')) === 'rejected';
      })->count();

      $hasOnlyPaid = $items->every(function ($c) {
        return strtolower((string) ($c->status ?? 'pending')) === 'paid';
      });
      $hasOnlyRejected = $items->every(function ($c) {
        return strtolower((string) ($c->status ?? 'pending')) === 'rejected';
      });
      $groupStatus = $hasOnlyPaid ? 'paid' : ($hasOnlyRejected ? 'rejected' : 'pending');

      $collapseId = 'referrer_' . preg_replace('/[^a-zA-Z0-9_]/', '_', (string)$referrerKey);
    @endphp

    <div class="card shadow-sm mb-3">
      <div class="card-body pb-2">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
          <div class="d-flex align-items-start gap-3">
            <div class="pt-1">
              <input class="form-check-input js-group-toggle"
                     type="checkbox"
                     data-group="{{ $collapseId }}"
                     aria-label="Select group">
            </div>
            <div>
              <div class="fw-bold">{{ $refName }}</div>
              <div class="text-muted small">{{ $refEmail }}</div>
            </div>
          </div>

          <div class="d-flex gap-2 align-items-center flex-wrap">
            <button type="button"
                    class="btn btn-outline-primary btn-sm js-group-pay-now"
                    data-group="{{ $collapseId }}">
              Pay Now
            </button>
            <button class="btn btn-outline-secondary btn-sm js-collapse-toggle"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="false"
                    aria-controls="{{ $collapseId }}">
              <span class="js-collapse-toggle-label">Expand</span>
            </button>
          </div>
        </div>

        <div class="mt-3 border rounded bg-white overflow-hidden">
          <div class="row g-0">
            <div class="col-md-4">
              <div class="p-3 h-100">
                <div class="fs-3 fw-bold">MYR {{ $totalMyr }}</div>
                <div class="text-muted">Total Commission</div>
              </div>
            </div>
            <div class="col-md-4 border-start">
              <div class="p-3 h-100 d-flex align-items-center gap-2">
                <i class="bi bi-receipt text-muted"></i>
                <div class="fw-semibold">{{ $txCount }} Transactions</div>
              </div>
            </div>
            <div class="col-md-4 d-flex align-items-center justify-content-center border-start">
              <div class="p-3 h-100 d-flex align-items-center gap-2">
                <span class="badge bg-success px-3 py-2">
                  <i class="bi bi-check2 me-1"></i> Paid {{ $paidCount }}
                </span>
                <span class="badge bg-danger px-3 py-2">
                  <i class="bi bi-x-lg me-1"></i> Rejected {{ $rejectedCount }}
                </span>
                <span class="badge bg-warning text-dark px-3 py-2">
                  <i class="bi bi-clock me-1"></i> Pending {{ $pendingCount }}
                </span>
              </div>
            </div>
          </div>
          
        </div>
      </div>

      <div class="collapse" id="{{ $collapseId }}">
        <div class="card-body pt-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 36px;"></th>
                  <th>Sub UUID</th>
                  <th>Referred</th>
                  <th>Code</th>
                  <th>Status</th>
                  <th class="text-end">Commission</th>
                  <th class="text-end" style="width: 90px;"></th>
                </tr>
              </thead>
              <tbody>
                @foreach($items as $c)
                  @php($st = strtolower((string)($c->status ?? 'pending')))
                  <tr>
                    <td>
                      <input class="form-check-input js-item"
                             type="checkbox"
                             name="ids[]"
                             value="{{ $c->id }}"
                             data-group="{{ $collapseId }}"
                             data-status="{{ $st }}">
                    </td>
                    <td class="small">{{ $c->subscription?->uuid ?? '—' }}</td>
                    <td>
                      <div class="fw-semibold">{{ $c->referred?->name ?? '—' }}</div>
                      @if($c->referred?->email)
                        <div class="text-muted small">{{ $c->referred?->email }}</div>
                      @endif
                    </td>
                    <td>{{ $c->affiliate_code_used }}</td>
                    <td>
                      @if($st === 'paid')
                        <span class="badge text-bg-success">Paid</span>
                      @elseif($st === 'rejected')
                        <span class="badge text-bg-danger">Rejected</span>
                      @else
                        <span class="badge text-bg-warning">Pending</span>
                      @endif
                    </td>
                    <td class="text-end">MYR {{ $c->commission_myr }}</td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.affiliate.commissions.show', $c->id) }}">View</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="text-muted small fw-semibold">Select All:</span>
              <button type="button" class="btn btn-sm btn-outline-secondary js-select-all" data-group="{{ $collapseId }}">All</button>
              <button type="button" class="btn btn-sm btn-outline-secondary js-select-none" data-group="{{ $collapseId }}">None</button>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="text-muted small fw-semibold">Update Status:</span>
              <select class="form-select form-select-sm js-status-select" data-default="paid" style="width: 160px;">
                <option value="pending">Mark as Pending</option>
                <option value="paid" selected>Mark as Paid</option>
                <option value="rejected">Mark as Rejected</option>
              </select>
              <button type="button"
                      class="btn btn-sm btn-primary js-submit-group"
                      data-group="{{ $collapseId }}">
                <i class="bi bi-check2"></i> Update Status
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="card shadow-sm">
      <div class="card-body text-muted">No commissions found.</div>
    </div>
  @endforelse

  <div class="card shadow-sm mt-4">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="text-muted">
        <span class="fw-semibold">Selected:</span> <span id="commissionSelectedCount">0</span>
      </div>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="text-muted fw-semibold">Bulk Update Status:</span>
        <select class="form-select form-select-sm js-status-select-global" style="width: 180px;">
          <option value="pending">Mark as Pending</option>
          <option value="paid" selected>Mark as Paid</option>
          <option value="rejected">Mark as Rejected</option>
        </select>
        <button type="button" class="btn btn-primary btn-sm js-submit-global">
          <i class="bi bi-check2"></i> Update Status
        </button>
      </div>
    </div>
  </div>
</form>

<div class="mt-3">{{ $commissions->links() }}</div>

<script>
  (function () {
    const form = document.getElementById('commissionBulkForm');
    const statusHidden = document.getElementById('commissionBulkStatus');
    const selectedCountEl = document.getElementById('commissionSelectedCount');
    if (!form || !statusHidden || !selectedCountEl) return;

    const qsa = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const qs = (sel, root = document) => root.querySelector(sel);

    function initCollapseToggleLabels() {
      // If Bootstrap collapse events are available, sync button labels Expand/Hide.
      qsa('[data-bs-toggle="collapse"][data-bs-target^="#"]', form).forEach(btn => {
        const target = btn.getAttribute('data-bs-target');
        if (!target) return;
        const panel = qs(target, document);
        if (!panel) return;

        const labelEl = btn.querySelector('.js-collapse-toggle-label') || btn;
        const setLabel = (isShown) => {
          if (labelEl) labelEl.textContent = isShown ? 'Hide' : 'Expand';
          btn.setAttribute('aria-expanded', isShown ? 'true' : 'false');
        };

        setLabel(panel.classList.contains('show'));

        panel.addEventListener('shown.bs.collapse', () => setLabel(true));
        panel.addEventListener('hidden.bs.collapse', () => setLabel(false));
      });
    }

    function setSelectedCount() {
      const count = qsa('input.js-item:checked', form).length;
      selectedCountEl.textContent = String(count);
      return count;
    }

    function setGroupChecked(groupId, checked) {
      qsa(`input.js-item[data-group="${groupId}"]`, form).forEach(cb => { cb.checked = checked; });
      setSelectedCount();
    }

    function uncheckOutsideGroup(groupId) {
      qsa('input.js-item:checked', form)
        .filter(cb => cb.getAttribute('data-group') !== groupId)
        .forEach(cb => { cb.checked = false; });
    }

    function ensureAnySelected() {
      const count = setSelectedCount();
      if (count > 0) return count;
      alert('Sila pilih sekurang-kurangnya satu transaksi.');
      return 0;
    }

    function statusLabel(status) {
      if (status === 'paid') return 'Paid';
      if (status === 'rejected') return 'Rejected';
      return 'Pending';
    }

    function confirmProceed(scopeLabel, status, count) {
      return confirm(`Confirm update ${count} transaksi (${scopeLabel}) kepada status: ${statusLabel(status)} ?`);
    }

    // Keep group toggle in sync (best-effort)
    form.addEventListener('change', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLInputElement)) return;
      if (!t.classList.contains('js-item')) return;

      const groupId = t.getAttribute('data-group');
      if (!groupId) return;

      const groupItems = qsa(`input.js-item[data-group="${groupId}"]`, form);
      const groupToggle = form.querySelector(`input.js-group-toggle[data-group="${groupId}"]`);
      if (groupToggle instanceof HTMLInputElement) {
        const allChecked = groupItems.length > 0 && groupItems.every(cb => cb.checked);
        groupToggle.checked = allChecked;
      }

      setSelectedCount();
    });

    qsa('input.js-group-toggle', form).forEach(toggle => {
      toggle.addEventListener('change', () => {
        const groupId = toggle.getAttribute('data-group');
        if (!groupId) return;
        setGroupChecked(groupId, toggle.checked);
      });
    });

    qsa('button.js-select-all', form).forEach(btn => {
      btn.addEventListener('click', () => {
        const groupId = btn.getAttribute('data-group');
        if (!groupId) return;
        setGroupChecked(groupId, true);

        const groupToggle = form.querySelector(`input.js-group-toggle[data-group="${groupId}"]`);
        if (groupToggle instanceof HTMLInputElement) groupToggle.checked = true;
      });
    });

    qsa('button.js-select-none', form).forEach(btn => {
      btn.addEventListener('click', () => {
        const groupId = btn.getAttribute('data-group');
        if (!groupId) return;
        setGroupChecked(groupId, false);

        const groupToggle = form.querySelector(`input.js-group-toggle[data-group="${groupId}"]`);
        if (groupToggle instanceof HTMLInputElement) groupToggle.checked = false;
      });
    });

    qsa('button.js-submit-group', form).forEach(btn => {
      btn.addEventListener('click', () => {
        const groupId = btn.getAttribute('data-group');
        if (!groupId) return;

        // For "per-user update", submit only selected in this group
        uncheckOutsideGroup(groupId);

        const card = btn.closest('.card') || form;
        const select = card.querySelector('select.js-status-select');
        const chosen = (select instanceof HTMLSelectElement) ? select.value : 'paid';
        statusHidden.value = chosen;

        const count = ensureAnySelected();
        if (!count) return;
        if (!confirmProceed('user', chosen, count)) return;
        form.submit();
      });
    });

    qsa('button.js-group-pay-now', form).forEach(btn => {
      btn.addEventListener('click', () => {
        const groupId = btn.getAttribute('data-group');
        if (!groupId) return;

        // Pay Now = select pending only in group, set status=paid, submit only this group
        qsa(`input.js-item[data-group="${groupId}"]`, form).forEach(cb => {
          cb.checked = (cb.getAttribute('data-status') || 'pending') === 'pending';
        });
        setSelectedCount();
        uncheckOutsideGroup(groupId);
        statusHidden.value = 'paid';

        const count = ensureAnySelected();
        if (!count) return;
        if (!confirmProceed('user', 'paid', count)) return;
        form.submit();
      });
    });

    const globalSelect = form.querySelector('select.js-status-select-global');
    const globalBtn = form.querySelector('button.js-submit-global');
    if (globalBtn) {
      globalBtn.addEventListener('click', () => {
        const chosen = (globalSelect instanceof HTMLSelectElement) ? globalSelect.value : 'paid';
        statusHidden.value = chosen;
        const count = ensureAnySelected();
        if (!count) return;
        if (!confirmProceed('bulk', chosen, count)) return;
        form.submit();
      });
    }

    initCollapseToggleLabels();
    setSelectedCount();
  })();
</script>
@endsection
