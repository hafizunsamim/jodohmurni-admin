<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\ClientJourneyLabels;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->get('q', ''));
        $gender  = $request->get('gender');
        $country = $request->get('country');

        return view('admin.clients.index', compact('q', 'gender', 'country'));
    }

    /**
     * DataTables server-side JSON for clients list.
     */
    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        if ($length === -1) {
            $length = 500;
        } elseif ($length < 1) {
            $length = 10;
        } elseif ($length > 100) {
            $length = 100;
        }

        $q       = trim((string) $request->get('q', ''));
        $gender  = $request->get('gender');
        $country = $request->get('country');

        if ($q === '') {
            $q = trim((string) $request->input('search.value', ''));
        }

        $recordsTotal = Client::onlyUsers()
            ->where('is_external_affiliate', false)
            ->count();

        $filteredQuery = $this->filteredClientsQuery($q, $gender, $country);
        $recordsFiltered = (clone $filteredQuery)->count();

        $orderColumn = (int) $request->input('order.0.column', 6);
        $orderDir    = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        // 0=#,1=name,2=membership,3=gender,4=country,5=laluan (tiada sort DB),6=joined,7=action
        $columnMap = [
            1 => 'name',
            2 => 'status_keahlian',
            3 => 'gender',
            4 => 'country',
            6 => 'created_at',
        ];

        $query = $this->filteredClientsQuery($q, $gender, $country);
        if (isset($columnMap[$orderColumn])) {
            $query->orderBy($columnMap[$orderColumn], $orderDir);
        } else {
            $query->orderByDesc('created_at');
        }

        $clients = $query->skip($start)->take($length)->get();

        [$earlyBirdActive, $underCap, $activeHypeUserIds] = $this->resolveHypeEarlyBirdContext($clients->pluck('id')->all());

        $rows = $clients->map(function (Client $c) use ($earlyBirdActive, $underCap, $activeHypeUserIds) {
            $nameHtml = '<span class="fw-semibold">'.e($c->name).'</span>';
            if ($c->nickname) {
                $nameHtml .= '<div class="text-muted small">'.e($c->nickname).'</div>';
            }
            $emailLine = ($c->email !== null && $c->email !== '') ? $c->email : '—';
            $phoneLine = ($c->phone !== null && $c->phone !== '') ? $c->phone : '—';
            $nameHtml .= '<div class="text-muted small">'.e($emailLine).'</div>';
            $nameHtml .= '<div class="text-muted small">'.e($phoneLine).'</div>';

            $m = $c->status_keahlian ?? 'LITE';
            $badgeClass = 'bg-secondary';
            if ($m === 'ACTIVE') {
                $badgeClass = 'bg-success';
            } elseif ($m === 'GRADUATE') {
                $badgeClass = 'bg-primary';
            } elseif ($m === 'HYPE') {
                $badgeClass = 'bg-warning text-dark';
            }
            $label = $m;
            if ($m === 'HYPE' && $earlyBirdActive && $underCap && in_array($c->id, $activeHypeUserIds, true)) {
                $label = 'HYPE (EARLY BIRD)';
            }
            $membershipHtml = '<span class="badge '.$badgeClass.'">'.e($label).'</span>';

            $pathHtml = ClientJourneyLabels::toHtml($c);

            $actionHtml = '<a class="btn btn-sm btn-outline-primary" href="'.e(route('admin.clients.show', $c->id)).'">View</a>';

            return [
                'name_html'       => $nameHtml,
                'membership_html' => $membershipHtml,
                'gender'          => $c->gender ? ucfirst($c->gender) : '—',
                'country'         => $c->country ?? '—',
                'path_html'       => $pathHtml,
                'joined'          => $c->created_at ? $c->created_at->format('d M Y') : '—',
                'action_html'     => $actionHtml,
            ];
        })->values()->all();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    public function show(string $id)
    {
        $client = Client::onlyUsers()
            ->where('is_external_affiliate', false)
            ->where('id', $id)
            ->firstOrFail();

        return view('admin.clients.show', compact('client'));
    }

    /**
     * @param  array<int, string>  $pageUserIds
     * @return array{0: bool, 1: bool, 2: array<int, string>}
     */
    private function resolveHypeEarlyBirdContext(array $pageUserIds): array
    {
        $earlyBirdActive   = false;
        $underCap          = true;
        $activeHypeUserIds = [];

        try {
            $hypePkg = SubscriptionPackage::query()->where('code', 'HYPE')->first();

            if (Schema::hasTable('settings')) {
                $v = DB::table('settings')->where('key', 'is_early_bird_active')->value('value');
                $earlyBirdActive = in_array($v, ['1', 'true', true], true);
            }

            if ($hypePkg) {
                $count = (int) Subscription::query()
                    ->where('package_id', $hypePkg->id)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->distinct('user_id')
                    ->count('user_id');
                $underCap = $count < 300;

                if ($pageUserIds !== []) {
                    $activeHypeUserIds = Subscription::query()
                        ->whereIn('user_id', $pageUserIds)
                        ->where('package_id', $hypePkg->id)
                        ->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                        })
                        ->distinct('user_id')
                        ->pluck('user_id')
                        ->all();
                }
            }
        } catch (\Throwable $e) {
            // fallback: badge guna status_keahlian sahaja
        }

        return [$earlyBirdActive, $underCap, $activeHypeUserIds];
    }

    private function filteredClientsQuery(string $q, mixed $gender, mixed $country)
    {
        return Client::onlyUsers()
            ->where('is_external_affiliate', false)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('nickname', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->when(in_array($gender, ['male', 'female'], true), fn ($query) => $query->where('gender', $gender))
            ->when(in_array($country, ['MY', 'ID', 'SG', 'BN'], true), fn ($query) => $query->where('country', $country));
    }
}
