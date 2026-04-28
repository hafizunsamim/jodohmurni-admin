@extends('admin.layout')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h3 class="fw-bold mb-0">Folder: {{ $folder }}</h3>
        <div class="text-muted">
            Base: <code>{{ env('TESTING_IMAGES_BASE') }}</code>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.images.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

@if(empty($images))
    <div class="alert alert-warning">No images in this folder.</div>
@else
    <div class="row g-3">
        @foreach($images as $url)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card shadow-sm h-100">
                    <a href="{{ $url }}" target="_blank" rel="noopener">
                        <img src="{{ $url }}" class="card-img-top" style="height:220px; object-fit:cover;">
                    </a>
                    <div class="card-body py-2">
                        <small class="text-muted" style="word-break:break-all;">{{ $url }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
