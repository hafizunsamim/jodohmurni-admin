<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProRequest;
use App\Models\ExternalAffiliateApplication;
use App\Models\Client;
use App\Models\UserAffiliateCode;
use App\Mail\ExternalAffiliateApprovedMail;
use App\Services\AffiliateTierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AffiliateProRequestAdminController extends Controller
{
    public function index(Request $request, AffiliateTierService $tiers)
    {
        $status = trim((string) $request->get('status', 'pending'));
        $q = trim((string) $request->get('q', ''));

        $allowed = ['pending', 'approved', 'rejected', 'all'];
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }

        $registered = AffiliateProRequest::query()
            ->with(['user', 'reviewer'])
            ->when($status !== 'all', fn($qq) => $qq->where('status', $status))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->whereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByRaw("CASE WHEN status='pending' THEN 0 WHEN status='rejected' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            // Untuk DataTables client-side pagination/search, kita load set data (cap untuk elak terlalu besar)
            ->limit(1000)
            ->get();

        $external = ExternalAffiliateApplication::query()
            ->when($status !== 'all', fn($qq) => $qq->where('status', $status))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('full_name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderByRaw("CASE WHEN status='pending' THEN 0 WHEN status='rejected' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->limit(1000)
            ->get();

        // decorate registered tier in memory
        $registered->transform(function ($r) use ($tiers) {
            $r->current_affiliate_tier = $r->user ? $tiers->tierFor($r->user) : null;
            $r->request_type = 'registered';
            return $r;
        });
        $external->transform(function ($r) {
            $r->request_type = 'external';
            return $r;
        });

        $rows = $registered->concat($external)->sortByDesc('id')->values();

        return view('admin.affiliate.pro_requests.index', compact('rows', 'status', 'q'));
    }

    public function show($id, AffiliateTierService $tiers)
    {
        $req = AffiliateProRequest::query()
            ->with(['user', 'reviewer'])
            ->findOrFail($id);

        $tier = $req->user ? $tiers->tierFor($req->user) : null;

        return view('admin.affiliate.pro_requests.show', compact('req', 'tier'));
    }

    public function approve(Request $request, $id)
    {
        $reqRow = AffiliateProRequest::query()->with('user')->findOrFail($id);

        if ($reqRow->status !== AffiliateProRequest::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Permohonan ini sudah diproses.']);
        }

        // Approve: feedback optional
        $data = $request->validate([
            'admin_feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $reqRow->update([
            'status' => AffiliateProRequest::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by_admin_id' => auth('admin')->id(),
            'admin_feedback' => isset($data['admin_feedback']) ? trim((string)$data['admin_feedback']) : null,
        ]);

        if ($reqRow->user) {
            $reqRow->user->update([
                'affiliate_pro_approved_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.affiliate.pro_requests.show', $reqRow->id)
            ->with('success', 'Permohonan telah diluluskan. User kini Affiliate Pro.');
    }

    public function reject(Request $request, $id)
    {
        $reqRow = AffiliateProRequest::query()->with('user')->findOrFail($id);

        if ($reqRow->status !== AffiliateProRequest::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Permohonan ini sudah diproses.']);
        }

        $data = $request->validate([
            'admin_feedback' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $reqRow->update([
            'status' => AffiliateProRequest::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by_admin_id' => auth('admin')->id(),
            'admin_feedback' => trim((string)$data['admin_feedback']),
        ]);

        return redirect()
            ->route('admin.affiliate.pro_requests.show', $reqRow->id)
            ->with('success', 'Permohonan telah ditolak.');
    }

    /**
     * Single review endpoint for UI (Lulus / Tolak + Ulasan).
     * Validation: if reject, feedback required.
     */
    public function review(Request $request, $id)
    {
        $reqRow = AffiliateProRequest::query()->with('user')->findOrFail($id);

        if ($reqRow->status !== AffiliateProRequest::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Permohonan ini sudah diproses.']);
        }

        $data = $request->validate([
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'admin_feedback' => ['nullable', 'string', 'min:5', 'max:2000'],
        ]);

        $decision = $data['decision'];
        $feedback = isset($data['admin_feedback']) ? trim((string) $data['admin_feedback']) : '';

        if ($decision === 'rejected' && $feedback === '') {
            return back()->withErrors(['admin_feedback' => 'Ulasan wajib diisi jika permohonan ditolak.'])->withInput();
        }

        if ($decision === 'approved') {
            $reqRow->update([
                'status' => AffiliateProRequest::STATUS_APPROVED,
                'reviewed_at' => now(),
                'reviewed_by_admin_id' => auth('admin')->id(),
                'admin_feedback' => $feedback !== '' ? $feedback : null,
            ]);

            if ($reqRow->user) {
                $reqRow->user->update([
                    'affiliate_pro_approved_at' => now(),
                ]);
            }

            return back()->with('success', 'Permohonan telah diluluskan. User kini Affiliate Pro.');
        }

        // rejected
        $reqRow->update([
            'status' => AffiliateProRequest::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by_admin_id' => auth('admin')->id(),
            'admin_feedback' => $feedback,
        ]);

        return back()->with('success', 'Permohonan telah ditolak.');
    }

    /**
     * Review external affiliate application (no-account).
     * Approve: create client user, mark as external Affiliate Pro, generate affiliate code, email credentials.
     */
    public function reviewExternal(Request $request, $id)
    {
        $row = ExternalAffiliateApplication::query()->findOrFail($id);

        if (strtolower((string) $row->status) !== ExternalAffiliateApplication::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Permohonan ini sudah diproses.']);
        }

        $data = $request->validate([
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'admin_feedback' => ['nullable', 'string', 'min:5', 'max:2000'],
        ]);

        $decision = $data['decision'];
        $feedback = isset($data['admin_feedback']) ? trim((string) $data['admin_feedback']) : '';

        if ($decision === 'rejected' && $feedback === '') {
            return back()->withErrors(['admin_feedback' => 'Ulasan wajib diisi jika permohonan ditolak.'])->withInput();
        }

        if ($decision === 'rejected') {
            $row->update([
                'status' => ExternalAffiliateApplication::STATUS_REJECTED,
                'reviewed_at' => now(),
                'reviewed_by_admin_id' => auth('admin')->id(),
                'admin_feedback' => $feedback,
            ]);

            return back()->with('success', 'Permohonan external telah ditolak.');
        }

        // approved
        $plainPassword = null;
        $affiliateCode = null;
        $loginUrl = null;
        $affiliateLink = null;

        try {
            DB::transaction(function () use ($id, &$plainPassword, &$affiliateCode, &$loginUrl, &$affiliateLink, $feedback) {
                $rowLocked = ExternalAffiliateApplication::query()->lockForUpdate()->findOrFail($id);

                if (strtolower((string) $rowLocked->status) === ExternalAffiliateApplication::STATUS_APPROVED && !empty($rowLocked->user_id)) {
                    // idempotent: already approved
                    $affiliateCode = UserAffiliateCode::query()->where('user_id', $rowLocked->user_id)->value('code');
                    $base = rtrim((string) config('app.frontend_url'), '/');
                    $loginUrl = $base . '/affiliate';
                    $affiliateLink = $base . '/?ref=' . $affiliateCode;
                    return;
                }

                $email = mb_strtolower(trim((string) $rowLocked->email));
                if (Client::query()->where('email', $email)->exists()) {
                    throw new \RuntimeException('Email sudah wujud dalam users. Tidak boleh approve external applicant.');
                }

                $plainPassword = Str::random(10);

                // nickname unique (required by some UI flows)
                do {
                    $nickname = Str::upper(Str::random(7));
                } while (Client::query()->where('nickname', $nickname)->exists());

                $client = Client::create([
                    'id' => (string) Str::uuid(),
                    'role' => 'user',
                    'name' => trim((string) $rowLocked->full_name),
                    'nickname' => $nickname,
                    'email' => $email,
                    'phone' => (string) $rowLocked->phone_number,
                    'password' => Hash::make($plainPassword),
                    'status_keahlian' => 'LITE',
                    'affiliate_pro_approved_at' => now(),
                    'is_external_affiliate' => true,
                    'lite_education_seen' => true,
                ]);

                // ensure affiliate code exists
                $existing = UserAffiliateCode::query()->where('user_id', $client->id)->first();
                if ($existing) {
                    $affiliateCode = $existing->code;
                } else {
                    do {
                        $code = strtoupper(Str::random(10));
                        $exists = UserAffiliateCode::query()->where('code', $code)->exists();
                    } while ($exists);
                    $created = UserAffiliateCode::create([
                        'user_id' => $client->id,
                        'code' => $code,
                        'clicks' => 0,
                    ]);
                    $affiliateCode = $created->code;
                }

                $rowLocked->update([
                    'status' => ExternalAffiliateApplication::STATUS_APPROVED,
                    'approved_at' => now(),
                    'reviewed_at' => now(),
                    'reviewed_by_admin_id' => auth('admin')->id(),
                    'admin_feedback' => $feedback !== '' ? $feedback : null,
                    'user_id' => $client->id,
                ]);

                $base = rtrim((string) config('app.frontend_url'), '/');
                $loginUrl = $base . '/affiliate';
                $affiliateLink = $base . '/?ref=' . $affiliateCode;
            }, 3);
        } catch (\Throwable $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        // Send email (do not break approval on failure)
        try {
            if ($plainPassword && $affiliateCode) {
                Mail::to($row->email)->send(new ExternalAffiliateApprovedMail(
                    (string) $row->full_name,
                    (string) $row->email,
                    (string) $plainPassword,
                    (string) $loginUrl,
                    (string) $affiliateLink
                ));
            }
        } catch (\Throwable $mailEx) {
            Log::error('External affiliate approval email failed', [
                'external_application_id' => $row->id,
                'email' => $row->email,
                'error' => $mailEx->getMessage(),
            ]);

            return back()->with('success', 'Permohonan external telah diluluskan. (Amaran: Email gagal dihantar, sila semak log.)');
        }

        return back()->with('success', 'Permohonan external telah diluluskan. Akaun Affiliate Pro telah dicipta dan email dihantar.');
    }
}

