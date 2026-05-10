<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\HelpdeskTicket;
use Illuminate\Support\Facades\DB;
use App\Models\AffiliateCommission;
use App\Models\AffiliateProRequest;
use App\Models\ExternalAffiliateApplication;
use App\Services\Ga4AnalyticsService;

class DashboardController extends Controller
{
public function index()
{
    $totalUsers  = Client::onlyUsers()->where('is_external_affiliate', false)->count();
    $totalMale   = Client::onlyUsers()->where('is_external_affiliate', false)->where('gender', 'male')->count();
    $totalFemale = Client::onlyUsers()->where('is_external_affiliate', false)->where('gender', 'female')->count();

    $totalAffiliatePro = Client::onlyUsers()
        ->whereNotNull('affiliate_pro_approved_at')
        ->count();
    $totalExternalAffiliatePro = Client::onlyUsers()
        ->whereNotNull('affiliate_pro_approved_at')
        ->where('is_external_affiliate', true)
        ->count();
    $totalInternalAffiliatePro = Client::onlyUsers()
        ->whereNotNull('affiliate_pro_approved_at')
        ->where('is_external_affiliate', false)
        ->count();

    $recentUsers = Client::onlyUsers()
        ->where('is_external_affiliate', false)
        ->orderByDesc('created_at')
        ->limit(10)
        ->get();

    $topCommission = AffiliateCommission::query()
        ->select('referrer_user_id',
            DB::raw('SUM(commission_sen) as total_commission_sen'),
            DB::raw('COUNT(*) as total_commissions')
        )
        ->groupBy('referrer_user_id')
        ->orderByDesc('total_commission_sen')
        ->first();

    $topCommissionUser = null;
    $topCommissionMyr = null;
    $topCommissionCount = 0;

    if ($topCommission) {
        $topCommissionUser = Client::query()
            ->select('id','name','email')
            ->where('id', $topCommission->referrer_user_id)
            ->first();

        $topCommissionMyr = number_format(((int)$topCommission->total_commission_sen) / 100, 2);
        $topCommissionCount = (int) $topCommission->total_commissions;
    }

    $countryRows = Client::onlyUsers()
        ->select('country', DB::raw('COUNT(*) as total'))
        ->where('is_external_affiliate', false)
        ->whereNotNull('country')
        ->groupBy('country')
        ->orderByDesc('total')
        ->get();

    $countryLabels = [];
    $countryTotals = [];


    $map = ['MY' => 'Malaysia', 'ID' => 'Indonesia', 'SG' => 'Singapore', 'BN' => 'Brunei'];

    foreach ($countryRows as $r) {
        $code = $r->country;
        $countryLabels[] = $map[$code] ?? $code;
        $countryTotals[] = (int) $r->total;
    }

    $affiliateProPendingRegistered = AffiliateProRequest::query()
        ->where('status', AffiliateProRequest::STATUS_PENDING)
        ->count();

    $affiliateProPendingExternal = ExternalAffiliateApplication::query()
        ->where('status', ExternalAffiliateApplication::STATUS_PENDING)
        ->count();

    $affiliateProPendingTotal = (int) $affiliateProPendingRegistered + (int) $affiliateProPendingExternal;

    $helpdeskTicketsNeedingAttention = HelpdeskTicket::query()->unsolved()->count();

    $ga4EventReport = null;
    $ga4EventReportError = null;
    try {
        $propertyId = (string) config('analytics.ga4.property_id');
        $credentialsPath = (string) config('analytics.ga4.credentials_path');
        $oauthClientId = (string) config('analytics.ga4.oauth.client_id');
        $oauthClientSecret = (string) config('analytics.ga4.oauth.client_secret');
        $oauthRefreshToken = (string) config('analytics.ga4.oauth.refresh_token');

        $hasOauth = $oauthClientId !== '' && $oauthClientSecret !== '' && $oauthRefreshToken !== '';
        $hasServiceAccountJson = $credentialsPath !== '';

        if ($propertyId !== '' && ($hasOauth || $hasServiceAccountJson)) {
            $ga4EventReport = app(Ga4AnalyticsService::class)->eventCounts();
        }
    } catch (\Throwable $e) {
        $ga4EventReportError = $e->getMessage();
    }

    return view('admin.dashboard', compact(
        'totalUsers',
        'totalMale',
        'totalFemale',
        'totalAffiliatePro',
        'totalExternalAffiliatePro',
        'totalInternalAffiliatePro',
        'recentUsers',
        'topCommissionUser',
        'topCommissionMyr',
        'topCommissionCount',
        'countryLabels',
        'countryTotals',
        'affiliateProPendingRegistered',
        'affiliateProPendingExternal',
        'affiliateProPendingTotal',
        'helpdeskTicketsNeedingAttention',
        'ga4EventReport',
        'ga4EventReportError'
    ));
}
}
