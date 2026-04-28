<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;

class SubscriptionAdminController extends Controller
{
    /**
     * OPTIONAL:
     * Kalau kau nak paksa semua method dalam controller ni mesti lepas middleware,
     * kau boleh ON kan constructor ni.
     *
     * Tapi kalau routes dah letak Route::middleware('admin.auth')->group(...),
     * constructor ni tak wajib.
     */
    // public function __construct()
    // {
    //     $this->middleware('admin.auth');
    // }

    // ===== Packages =====
    public function packages(Request $request)
    {
        // Kalau kau nak double safety walaupun middleware dah ada:
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login.form');
        }

        $q = trim((string) $request->get('q', ''));
        $active = $request->get('active'); // '1' / '0' / null

        $packages = SubscriptionPackage::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->when(in_array((string) $active, ['0', '1'], true), function ($query) use ($active) {
                $query->where('is_active', (int) $active);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.subscriptions.packages', compact('packages', 'q', 'active'));
    }

    public function packageShow($id)
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login.form');
        }

        $package = SubscriptionPackage::query()->findOrFail($id);

        $recentSubs = Subscription::query()
            ->with(['user'])
            ->where('package_id', $package->id)
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('admin.subscriptions.package_show', compact('package', 'recentSubs'));
    }

    // ===== Subscriptions =====
    public function subscriptions(Request $request)
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login.form');
        }

        $q = trim((string) $request->get('q', '')); // uuid / email / name / code
        $status = $request->get('status'); // pending/active/cancelled/expired/null

        $subs = Subscription::query()
            ->with(['user', 'package', 'referrer'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('uuid', 'like', "%{$q}%")
                      ->orWhere('affiliate_code_used', 'like', "%{$q}%");
                })
                ->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                })
                ->orWhereHas('package', function ($p) use ($q) {
                    $p->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->when(in_array((string) $status, ['pending', 'active', 'cancelled', 'expired'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.subscriptions.index', compact('subs', 'q', 'status'));
    }

    public function subscriptionShow($id)
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login.form');
        }

        $sub = Subscription::query()
            ->with(['user', 'package', 'referrer', 'commission'])
            ->findOrFail($id);

        return view('admin.subscriptions.show', compact('sub'));
    }
}
