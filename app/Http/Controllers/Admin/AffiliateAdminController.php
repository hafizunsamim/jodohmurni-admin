<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\UserAffiliateCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AffiliateAdminController extends Controller
{
    public function codes(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $codes = UserAffiliateCode::query()
            ->with(['user'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('code', 'like', "%{$q}%")
                      ->orWhereHas('user', fn($u) => $u->where('name','like',"%{$q}%")->orWhere('email','like',"%{$q}%"));
            })
            ->orderByDesc('clicks')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.affiliate.codes', compact('codes', 'q'));
    }

    public function codeShow($id)
    {
        $code = UserAffiliateCode::query()->with('user')->findOrFail($id);

        $recentComms = AffiliateCommission::query()
            ->with(['referred','subscription'])
            ->where('referrer_user_id', $code->user_id)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('admin.affiliate.code_show', compact('code', 'recentComms'));
    }

    public function commissions(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $minSen = $request->get('min_sen');
        $status = strtolower((string) $request->get('status', 'all'));
        $dateFrom = (string) $request->get('date_from', '');
        $dateTo = (string) $request->get('date_to', '');

        $base = AffiliateCommission::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('affiliate_code_used', 'like', "%{$q}%")
                    ->orWhereHas('referrer', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('referred', fn($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('subscription', fn($s) => $s->where('uuid', 'like', "%{$q}%"));
            })
            ->when(is_numeric($minSen), fn($query) => $query->where('commission_sen', '>=', (int) $minSen))
            ->when(in_array($status, AffiliateCommission::allowedStatuses(), true), fn($query) => $query->where('status', $status))
            ->when($dateFrom !== '', function ($query) use ($dateFrom) {
                try {
                    $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                } catch (\Throwable $e) {
                    // ignore invalid date
                }
            })
            ->when($dateTo !== '', function ($query) use ($dateTo) {
                try {
                    $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                } catch (\Throwable $e) {
                    // ignore invalid date
                }
            });

        $totalTransactions = (clone $base)->count();
        $totalReferrers = (clone $base)->whereNotNull('referrer_user_id')->distinct('referrer_user_id')->count('referrer_user_id');
        $totalPendingSen = (int) (clone $base)->where('status', AffiliateCommission::STATUS_PENDING)->sum('commission_sen');

        $commissions = (clone $base)
            ->with(['referrer', 'referred', 'subscription'])
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $totalPendingMyr = number_format($totalPendingSen / 100, 2);

        return view('admin.affiliate.commissions', compact(
            'commissions',
            'q',
            'minSen',
            'totalTransactions',
            'totalReferrers',
            'totalPendingMyr'
        ));
    }

    public function commissionShow($id)
    {
        $commission = AffiliateCommission::query()
            ->with(['referrer','referred','subscription'])
            ->findOrFail($id);

        return view('admin.affiliate.commission_show', compact('commission'));
    }

    public function commissionUpdateStatus(Request $request, $id)
    {
        $commission = AffiliateCommission::query()->findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', AffiliateCommission::allowedStatuses())],
        ]);

        $commission->update([
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('admin.affiliate.commissions.show', $commission->id)
            ->with('success', 'Status komisen berjaya dikemaskini.');
    }

    public function commissionBulkUpdateStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer'],
            'status' => ['required', 'string', 'in:' . implode(',', AffiliateCommission::allowedStatuses())],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['ids'] ?? [])));
        if (count($ids) < 1) {
            return redirect()
                ->route('admin.affiliate.commissions')
                ->with('error', 'Sila pilih sekurang-kurangnya satu transaksi untuk bulk update.');
        }

        AffiliateCommission::query()
            ->whereIn('id', $ids)
            ->update(['status' => $data['status']]);

        return redirect()
            ->route('admin.affiliate.commissions')
            ->with('success', 'Status komisen berjaya dikemaskini (bulk).');
    }
}
