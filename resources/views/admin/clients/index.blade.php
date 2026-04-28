@extends('admin.layout')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="fw-bold mb-0">Clients</h3>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form id="clients-filter-form" class="row g-2 align-items-end" action="#" method="get">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control"
                       placeholder="name / nickname / email / phone" autocomplete="off">
            </div>

            <div class="col-md-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">All</option>
                    <option value="male"   {{ $gender === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $gender === 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Country</label>
                <select name="country" class="form-select">
                    <option value="">All</option>
                    <option value="MY" {{ $country === 'MY' ? 'selected' : '' }}>MY</option>
                    <option value="ID" {{ $country === 'ID' ? 'selected' : '' }}>ID</option>
                    <option value="SG" {{ $country === 'SG' ? 'selected' : '' }}>SG</option>
                    <option value="BN" {{ $country === 'BN' ? 'selected' : '' }}>BN</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i> Filter
                </button>
                <button type="button" class="btn btn-outline-secondary" id="clients-filter-reset" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="clients-table" class="table align-middle w-100">
                <thead>
                    <tr>
                        <th class="text-muted text-nowrap" style="width: 3rem;">#</th>
                        <th>Name</th>
                        <th>Membership</th>
                        <th>Gender</th>
                        <th>Country</th>
                        <th>Laluan</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
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
    const $form = $('#clients-filter-form');
    // Guna path relatif supaya AJAX sentiasa ke domain semasa (elak APP_URL salah di .env)
    const dataUrl = '/clients/data';

    const table = $('#clients-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[6, 'desc']],
        ajax: {
            url: dataUrl,
            data: function (d) {
                d.q = $form.find('input[name="q"]').val();
                d.gender = $form.find('select[name="gender"]').val();
                d.country = $form.find('select[name="country"]').val();
            },
            error: function (xhr) {
                console.error('Clients table AJAX failed', xhr.status, xhr.responseText?.slice?.(0, 500));
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-muted small',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'name_html', name: 'name', orderable: true, render: function (d) { return d; } },
            { data: 'membership_html', name: 'status_keahlian', orderable: true, render: function (d) { return d; } },
            { data: 'gender', name: 'gender', orderable: true },
            { data: 'country', name: 'country', orderable: true },
            { data: 'path_html', name: 'path', orderable: false, searchable: false, render: function (d) { return d; } },
            { data: 'joined', name: 'created_at', orderable: true },
            { data: 'action_html', name: 'action', orderable: false, searchable: false, className: 'text-end', render: function (d) { return d; } }
        ],
        columnDefs: [
            { responsivePriority: 1, targets: 1 }, // name
            { responsivePriority: 2, targets: 2 }, // membership
            { responsivePriority: 3, targets: 7 }, // action
        ],
        language: {
            emptyTable: 'No clients found.',
            zeroRecords: 'No clients found.'
        },
        dom: '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip'
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        const params = new URLSearchParams(new FormData($form[0]));
        history.replaceState(null, '', window.location.pathname + (params.toString() ? ('?' + params.toString()) : ''));
        table.ajax.reload();
    });

    $('#clients-filter-reset').on('click', function () {
        $form[0].reset();
        history.replaceState(null, '', window.location.pathname);
        table.ajax.reload();
    });
})();
</script>
@endpush
