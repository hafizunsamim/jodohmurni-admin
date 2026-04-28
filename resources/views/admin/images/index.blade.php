@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h3 class="fw-bold mb-0">All Image Folders (Testing)</h3>
        <div class="text-muted">
            Source: <code>{{ env('TESTING_IMAGES_PATH') }}</code>
        </div>
    </div>
</div>

@if(!empty($error))
    <div class="alert alert-danger">{{ $error }}</div>
@endif

<form method="GET" action="{{ route('admin.images.index') }}" class="mb-3">
    <div class="input-group" style="max-width:420px;">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Search folder name...">
        <button class="btn btn-primary" type="submit">Search</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.images.index') }}">Reset</a>
    </div>
</form>

@if(empty($folders))
    <div class="alert alert-warning">No folders found.</div>
@else
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 55%;">Folder</th>
                            <th style="width: 15%;">Images</th>
                            <th style="width: 30%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($folders as $f)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $f['name'] }}</div>
                                </td>
                                <td>{{ $f['count'] }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('admin.images.folder', ['folder' => $f['name']]) }}">
                                       View Images
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
