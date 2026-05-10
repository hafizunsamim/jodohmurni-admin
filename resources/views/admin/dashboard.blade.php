@extends('admin.layout')

@section('content')
<h3 class="mb-4 fw-bold">Dashboard Overview</h3>

@if(($affiliateProPendingTotal ?? 0) > 0)
    <div class="alert alert-warning d-flex align-items-start justify-content-between gap-3" role="alert">
        <div>
            <div class="fw-semibold">
                <i class="bi bi-bell-fill me-1"></i>
                Permohonan Affiliate Pro baru
            </div>
            <div class="small">
                Total pending: <span class="fw-semibold">{{ number_format($affiliateProPendingTotal) }}</span>
                <span class="text-muted">
                    (Registered: {{ number_format($affiliateProPendingRegistered ?? 0) }},
                    External: {{ number_format($affiliateProPendingExternal ?? 0) }})
                </span>
            </div>
        </div>
        <div class="flex-shrink-0">
            <a class="btn btn-sm btn-dark"
               href="{{ route('admin.affiliate.pro_requests.index', ['status' => 'pending']) }}">
                Semak sekarang
            </a>
        </div>
    </div>
@endif

@if(($helpdeskTicketsNeedingAttention ?? 0) > 0)
    <div class="alert alert-info d-flex align-items-start justify-content-between gap-3 border-info" role="alert">
        <div>
            <div class="fw-semibold">
                <i class="bi bi-headset me-1"></i>
                Helpdesk memerlukan perhatian
            </div>
            <div class="small">
                Terdapat <span class="fw-semibold">{{ number_format($helpdeskTicketsNeedingAttention) }}</span>
                tiket belum selesai (terbuka / menunggu semakan).
            </div>
        </div>
        <div class="flex-shrink-0">
            <a class="btn btn-sm btn-primary"
               href="{{ route('admin.helpdesk.index', ['tab' => 'open']) }}">
                Lihat helpdesk
            </a>
        </div>
    </div>
@endif

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Total Users</h6>
                <h3>{{ number_format($totalUsers) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Total Male</h6>
                <h3>{{ number_format($totalMale) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Total Female</h6>
                <h3>{{ number_format($totalFemale) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Top Commission Earner</h6>

                @if($topCommissionUser)
                    <div class="fw-semibold">{{ $topCommissionUser->name }}</div>
                    <div class="text-muted small">{{ $topCommissionUser->email }}</div>
                    <div class="mt-2">
                        <span class="badge bg-success">MYR {{ $topCommissionMyr }}</span>
                        <span class="badge bg-secondary">{{ $topCommissionCount }} commissions</span>
                    </div>
                @else
                    <div class="text-muted">No commission records yet.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Affiliate Pro</h6>

                <div class="d-flex align-items-end justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="small text-muted">Total Affiliate Pro</div>
                        <div class="h3 mb-0">{{ number_format($totalAffiliatePro ?? 0) }}</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Breakdown</div>
                        <div class="d-flex gap-2 flex-wrap justify-content-end">
                            <span class="badge bg-dark">
                                External: {{ number_format($totalExternalAffiliatePro ?? 0) }}
                            </span>
                            <span class="badge bg-primary">
                                Internal: {{ number_format($totalInternalAffiliatePro ?? 0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <h6 class="text-muted mb-1">Google Analytics (GA4) — Event Summary</h6>
                        <div class="small text-muted">
                            @if(!empty($ga4EventReport))
                                Range: {{ $ga4EventReport['start_date'] }} → {{ $ga4EventReport['end_date'] }}
                            @else
                                Not configured
                            @endif
                        </div>
                    </div>
                </div>

                @if(!empty($ga4EventReportError))
                    <div class="alert alert-warning mt-3 mb-0" role="alert">
                        {{ $ga4EventReportError }}
                    </div>
                @elseif(!empty($ga4EventReport))
                    @php
                        $ga4EventLabels = [
                            'jm_homepage_visit' => 'Bilangan pengguna yang melawat homepage',
                            'sign_up' => 'Bilangan user yang mendaftar akaun',
                            'login' => 'Bilangan user yang log masuk',
                            'subscription_package_click' => 'Bilangan user yang click subscription',
                            'purchase' => 'Bilangan user yang berjaya subscribe package',
                            'view_profile' => 'Bilangan user yang melihat profil',
                            'like_profile' => 'Bilangan user yang like profile',
                            'match_success' => 'Bilangan user yang berjaya matching pasangan',
                        ];
                    @endphp
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th class="text-end">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($ga4EventReport['rows'] ?? []) as $row)
                                    <tr>
                                        @php($eventKey = (string)($row['event_name'] ?? ''))
                                        <td class="fw-semibold">
                                            {{ $ga4EventLabels[$eventKey] ?? ($eventKey ?: '—') }}
                                        </td>
                                        <td class="text-end">{{ number_format((int)($row['event_count'] ?? 0)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mt-3 small text-muted">
                        Set <code>GA4_PROPERTY_ID</code> dan OAuth env (<code>GA4_OAUTH_CLIENT_ID</code>, <code>GA4_OAUTH_CLIENT_SECRET</code>, <code>GA4_OAUTH_REFRESH_TOKEN</code>) dalam <code>.env</code> untuk aktifkan widget ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>




<div class="row g-4 mt-1">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body " >
                <h5 class="mb-3">Recent Users</h5>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Country</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $u)
                                <tr>
                                    <td class="fw-semibold">{{ $u->name }}</td>
                                    <td>{{ $u->gender ? ucfirst($u->gender) : '—' }}</td>
                                    <td>{{ $u->country ?? '—' }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td>{{ $u->created_at ? $u->created_at->format('d M Y, H:i') : '—' }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('admin.clients.show', $u->id) }}">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-muted mb-0">Clients by Country</h6>
                    <span class="text-muted small">Total: {{ number_format(array_sum($countryTotals ?? [])) }}</span>
                </div>
                <div id="clientsByCountryChart" style="height: 260px;"></div>
            </div>
        </div>
    </div>


</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  (function () {
    const labels = @json($countryLabels ?? []);
    const totals = @json($countryTotals ?? []);

    const el = document.querySelector("#clientsByCountryChart");
    if (!el) return;

    const options = {
      chart: {
        type: "bar",
        height: 260,
        toolbar: { show: false }
      },
      series: [{
        name: "Clients",
        data: totals
      }],
      xaxis: {
        categories: labels,
        labels: { rotate: -15 }
      },
      dataLabels: {
        enabled: true
      },
      plotOptions: {
        bar: {
          borderRadius: 6,
          columnWidth: "45%"
        }
      },
      tooltip: {
        y: {
          formatter: (val) => `${val} clients`
        }
      }
    };

    const chart = new ApexCharts(el, options);
    chart.render();
  })();
</script>



@endsection