@extends('admin.layout')

@section('content')
@php
    $membershipRaw = $client->status_keahlian ?? 'LITE';
    $membershipLabel = $membershipRaw;
    $membershipClass = 'bg-secondary';

    if ($membershipRaw === 'ACTIVE') $membershipClass = 'bg-success';
    elseif ($membershipRaw === 'GRADUATE') $membershipClass = 'bg-primary';
    elseif ($membershipRaw === 'HYPE') $membershipClass = 'bg-warning text-dark';

    // HYPE (EARLY BIRD): detect dari DB admin (settings + subscription aktif pakej HYPE)
    if ($membershipRaw === 'HYPE') {
        try {
            $hypePkg = \App\Models\SubscriptionPackage::query()->where('code', 'HYPE')->first();
            $hasActiveHype = false;
            if ($hypePkg) {
                $hasActiveHype = \App\Models\Subscription::query()
                    ->where('user_id', $client->id)
                    ->where('package_id', $hypePkg->id)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->exists();
            }

            $earlyBirdActive = false;
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $v = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'is_early_bird_active')->value('value');
                $earlyBirdActive = in_array($v, ['1', 'true', true], true);
            }

            $underCap = true;
            if ($hypePkg) {
                $count = (int) \App\Models\Subscription::query()
                    ->where('package_id', $hypePkg->id)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->distinct('user_id')
                    ->count('user_id');
                $underCap = $count < 300;
            }

            if ($hasActiveHype && $earlyBirdActive && $underCap) {
                $membershipLabel = 'HYPE (EARLY BIRD)';
            }
        } catch (\Throwable $e) {
            // fallback: kekal HYPE sahaja
        }
    }

    $photos = [
        $client->photo_1,
        $client->photo_2,
        $client->photo_3,
        $client->photo_4,
    ];
    // Gambar upload user disimpan di app utama (jodohmurni.com).
    // Domain admin mungkin tiada /storage access, jadi guna base URL app utama.
    $publicAppUrl = rtrim(env('PUBLIC_APP_URL', 'https://jodohmurni.com'), '/');

    $photoSrc = function ($p) use ($publicAppUrl) {
        if (!$p) return null;
        return \Illuminate\Support\Str::startsWith($p, ['http://', 'https://'])
            ? $p
            : ($publicAppUrl . '/' . ltrim($p, '/'));
    };
    $avatar = null;
    foreach ($photos as $p) {
        $avatar = $photoSrc($p);
        if ($avatar) break;
    }

    $mapUrl = null;
    if (!is_null($client->latitude) && !is_null($client->longitude)) {
        $mapUrl = 'https://www.google.com/maps?q=' . urlencode($client->latitude . ',' . $client->longitude) . '&output=embed';
    }
@endphp

<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-light border overflow-hidden flex-shrink-0"
             style="width:52px;height:52px;">
            @if($avatar)
                <img src="{{ $avatar }}" alt="avatar" style="width:100%;height:100%;object-fit:cover;">
            @else
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                    <i class="bi bi-person" style="font-size: 1.4rem;"></i>
                </div>
            @endif
        </div>

        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h3 class="fw-bold mb-0">{{ $client->name }}</h3>
                <span class="badge {{ $membershipClass }}">{{ $membershipLabel }}</span>
                @if($client->email_verified_at)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check-circle"></i> Verified
                    </span>
                @endif
            </div>
            <div class="text-muted">
                {{ $client->email }}
                @if($client->phone) • {{ $client->phone }} @endif
            </div>
        </div>
    </div>

    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-5 col-xl-4">
        <div class="card shadow-sm overflow-hidden">
            <div class="card-header bg-white border-0 pb-0">
                <div class="text-uppercase text-muted fw-semibold small">Primary Profile</div>
            </div>

            <div class="card-body pt-3">
                <div class="d-flex gap-3">
                    <div class="rounded-4 border bg-light overflow-hidden flex-shrink-0"
                         style="width: 170px; height: 220px;">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="profile photo" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                                <i class="bi bi-image" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                    </div>

                    <div class="flex-grow-1">
                        <div class="text-muted mb-2 fw-semibold">Profile Summary</div>

                        @php
                            $items = [
                                ['icon' => 'bi-envelope', 'label' => 'Nickname', 'value' => $client->nickname ?? '—'],
                                ['icon' => 'bi-gender-ambiguous', 'label' => 'Gender', 'value' => $client->gender ? ucfirst($client->gender) : '—'],
                                ['icon' => 'bi-heart', 'label' => 'Marital', 'value' => $client->marital_status ?? '—'],
                                ['icon' => 'bi-geo-alt', 'label' => 'State', 'value' => $client->state ?? '—'],
                                ['icon' => 'bi-map', 'label' => 'District', 'value' => $client->district ?? '—'],
                                ['icon' => 'bi-calendar-event', 'label' => 'DOB', 'value' => $client->date_of_birth ? $client->date_of_birth->format('d M Y') : '—'],
                            ];
                        @endphp

                        <div class="vstack gap-2">
                            @foreach($items as $it)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:34px;height:34px;">
                                        <i class="bi {{ $it['icon'] }} text-muted"></i>
                                    </div>
                                    <div class="small">
                                        <div class="text-muted lh-1">{{ $it['label'] }}</div>
                                        <div class="fw-semibold">{{ $it['value'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="bi bi-person-check"></i>
                                <span>Joined: {{ $client->created_at ? $client->created_at->format('d M Y, H:i') : '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-0 pt-0">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small">Email Verified</span>
                    <span class="small fw-semibold">
                        {{ $client->email_verified_at ? $client->email_verified_at->format('d M Y, H:i') : '—' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white border-0 pb-0">
                <div class="text-uppercase text-muted fw-semibold small">Photos</div>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex gap-3 overflow-auto pb-1" style="scrollbar-gutter: stable;">
                    @foreach($photos as $idx => $p)
                        @php $src = $photoSrc($p); @endphp
                        <div class="flex-shrink-0" style="width: 220px;">
                            <div class="rounded-4 border bg-light overflow-hidden" style="height: 150px;">
                                @if($src)
                                    <img src="{{ $src }}" alt="photo {{ $idx+1 }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <div class="text-muted small d-flex align-items-center justify-content-center h-100">
                                        No Photo
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-muted small mt-2">Total: {{ collect($photos)->filter()->count() }} photos</div>
            </div>
        </div>
    </div>

    <div class="col-lg-7 col-xl-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0 pb-0">
                <div class="text-uppercase text-muted fw-semibold small">Professional & Personal Details</div>
            </div>

            <div class="card-body pt-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                                <i class="bi bi-briefcase"></i> Occupation
                            </div>
                            <div class="fw-semibold">{{ $client->occupation_type ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                                <i class="bi bi-mortarboard"></i> Education
                            </div>
                            <div class="fw-semibold">{{ $client->education_level ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                                <i class="bi bi-heart-pulse"></i> Marital
                            </div>
                            <div class="fw-semibold">{{ $client->marital_status ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div class="rounded-4 border p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-uppercase text-muted fw-semibold small">Hobbies & Interests</div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active Hobbies</span>
                            </div>
                            <div class="text-muted small mb-1">Hobbies</div>
                            <div class="fw-semibold" style="white-space: pre-wrap;">{{ $client->hobbies ?? '—' }}</div>
                            <div class="text-muted small mt-3 mb-1">Social</div>
                            <div class="fw-semibold" style="white-space: pre-wrap;">{{ $client->social_activities ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="rounded-4 border p-3 h-100">
                            <div class="text-uppercase text-muted fw-semibold small mb-2">Location</div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="text-muted small">Latitude</div>
                                    <div class="fw-semibold">{{ $client->latitude ?? '—' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Longitude</div>
                                    <div class="fw-semibold">{{ $client->longitude ?? '—' }}</div>
                                </div>
                            </div>

                            <div class="rounded-4 overflow-hidden border bg-light" style="height: 220px;">
                                @if($mapUrl)
                                    <iframe
                                        src="{{ $mapUrl }}"
                                        width="100%"
                                        height="220"
                                        style="border:0"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        title="client location map"
                                    ></iframe>
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                        No location coordinates
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <div class="rounded-4 border p-3">
                            <div class="text-uppercase text-muted fw-semibold small mb-2">Other</div>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-muted small">Path</div>
                                    <div class="fw-semibold">{{ $client->path ?? '—' }}</div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-muted small">Country</div>
                                    <div class="fw-semibold">{{ $client->country ?? '—' }}</div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-muted small">State</div>
                                    <div class="fw-semibold">{{ $client->state ?? '—' }}</div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-muted small">District</div>
                                    <div class="fw-semibold">{{ $client->district ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
