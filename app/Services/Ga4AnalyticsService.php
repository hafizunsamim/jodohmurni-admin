<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Filter\InListFilter;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Auth\Credentials\UserRefreshCredentials;
use Illuminate\Support\Arr;
use RuntimeException;

class Ga4AnalyticsService
{
    /**
     * @return array{property_id: string, credentials_path: string, oauth: array{client_id: string, client_secret: string, refresh_token: string}, start_date: string, end_date: string, event_names: list<string>}
     */
    public function config(): array
    {
        return [
            'property_id' => (string) config('analytics.ga4.property_id', ''),
            'credentials_path' => (string) config('analytics.ga4.credentials_path', ''),
            'oauth' => [
                'client_id' => (string) config('analytics.ga4.oauth.client_id', ''),
                'client_secret' => (string) config('analytics.ga4.oauth.client_secret', ''),
                'refresh_token' => (string) config('analytics.ga4.oauth.refresh_token', ''),
            ],
            'start_date' => (string) config('analytics.ga4.default_start_date', '7daysAgo'),
            'end_date' => (string) config('analytics.ga4.default_end_date', 'today'),
            'event_names' => Arr::where(
                (array) config('analytics.ga4.event_names', []),
                fn ($v) => is_string($v) && $v !== ''
            ),
        ];
    }

    protected function makeClient(string $credentialsPath, array $oauth): BetaAnalyticsDataClient
    {
        // Prefer OAuth jika diset (untuk kes GA4 tak benarkan add service account sebagai user).
        $clientId = trim((string) ($oauth['client_id'] ?? ''));
        $clientSecret = trim((string) ($oauth['client_secret'] ?? ''));
        $refreshToken = trim((string) ($oauth['refresh_token'] ?? ''));

        if ($clientId !== '' && $clientSecret !== '' && $refreshToken !== '') {
            $creds = new UserRefreshCredentials(
                ['https://www.googleapis.com/auth/analytics.readonly'],
                [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken,
                ]
            );

            return new BetaAnalyticsDataClient([
                'credentials' => $creds,
            ]);
        }

        $path = trim($credentialsPath);
        if ($path !== '' && is_file($path)) {
            return new BetaAnalyticsDataClient([
                'credentials' => $path,
            ]);
        }

        throw new RuntimeException('Sila set GA4 OAuth (client id/secret/refresh token) atau GA4_CREDENTIALS_PATH yang sah.');
    }

    /**
     * @return array{rows: list<array{event_name: string, event_count: int}>, start_date: string, end_date: string}
     */
    public function eventCounts(?array $eventNames = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $cfg = $this->config();

        $propertyId = trim($cfg['property_id']);
        if ($propertyId === '') {
            throw new RuntimeException('GA4_PROPERTY_ID belum diset.');
        }

        $names = $eventNames ?? $cfg['event_names'];
        $names = array_values(array_filter($names, fn ($v) => is_string($v) && $v !== ''));
        if ($names === []) {
            throw new RuntimeException('Senarai event kosong.');
        }

        $start = $startDate ?? $cfg['start_date'];
        $end = $endDate ?? $cfg['end_date'];

        $client = $this->makeClient((string) ($cfg['credentials_path'] ?? ''), (array) ($cfg['oauth'] ?? []));

        $request = (new RunReportRequest())
            ->setProperty('properties/' . $propertyId)
            ->setDateRanges([
                (new DateRange())
                    ->setStartDate($start)
                    ->setEndDate($end),
            ])
            ->setDimensions([
                (new Dimension())->setName('eventName'),
            ])
            ->setMetrics([
                (new Metric())->setName('eventCount'),
            ])
            ->setDimensionFilter(
                (new FilterExpression())->setFilter(
                    (new Filter())
                        ->setFieldName('eventName')
                        ->setInListFilter(
                            (new InListFilter())
                                ->setValues($names)
                                ->setCaseSensitive(true)
                        )
                )
            );

        $response = $client->runReport($request);

        $rows = [];
        foreach ($response->getRows() as $row) {
            $eventName = (string) ($row->getDimensionValues()[0]->getValue() ?? '');
            $countStr = (string) ($row->getMetricValues()[0]->getValue() ?? '0');
            $rows[] = [
                'event_name' => $eventName,
                'event_count' => (int) $countStr,
            ];
        }

        // Pastikan event yang tiada dalam response tetap muncul dengan 0
        $byName = [];
        foreach ($rows as $r) {
            $byName[$r['event_name']] = $r['event_count'];
        }
        $final = [];
        foreach ($names as $n) {
            $final[] = ['event_name' => $n, 'event_count' => (int) ($byName[$n] ?? 0)];
        }

        return [
            'rows' => $final,
            'start_date' => $start,
            'end_date' => $end,
        ];
    }
}

