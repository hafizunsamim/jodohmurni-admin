<?php

namespace App\Support;

use App\Models\Client;

/**
 * Label paparan laluan onboarding (lelaki: marital_status, wanita: path).
 */
class ClientJourneyLabels
{
    /**
     * @return array{title: string, subtitle: string}|null
     */
    public static function forClient(Client $c): ?array
    {
        $gender = $c->gender;
        if ($gender === 'female') {
            $key = $c->path;
            if ($key !== null && $key !== '' && isset(self::WANITA[$key])) {
                return self::WANITA[$key];
            }

            return null;
        }

        if ($gender === 'male') {
            $key = $c->marital_status;
            if ($key !== null && $key !== '' && isset(self::MALE[$key])) {
                return self::MALE[$key];
            }

            return null;
        }

        return null;
    }

    public static function toHtml(Client $c): string
    {
        $row = self::forClient($c);
        if ($row === null) {
            return '—';
        }

        return '<div class="fw-semibold">'.e($row['title']).'</div>'
            .'<div class="text-muted small">'.e($row['subtitle']).'</div>';
    }

    /** Lelaki — monogami & poligami (nilai `marital_status`). */
    private const MALE = [
        'single'        => ['title' => 'BUJANG', 'subtitle' => 'Belum pernah berkahwin'],
        'divorced'      => ['title' => 'DUDA (CERAI)', 'subtitle' => 'Pernah berkahwin & telah berpisah'],
        'widowed'       => ['title' => 'DUDA (KEMATIAN ISTERI)', 'subtitle' => 'Kehilangan pasangan'],
        'ex_polygamous' => ['title' => 'DUDA (PERNAH POLIGAMI)', 'subtitle' => 'Berpengalaman dalam poligami'],
        'married_1'     => ['title' => 'SATU ISTERI', 'subtitle' => 'Sedang berkahwin dengan seorang isteri'],
        'married_2'     => ['title' => 'DUA ISTERI', 'subtitle' => 'Sedang berkahwin dengan 2 orang isteri'],
        'married_3'     => ['title' => 'TIGA ISTERI', 'subtitle' => 'Sedang berkahwin dengan 3 orang isteri'],
    ];

    /** Wanita (nilai `path`). */
    private const WANITA = [
        'wanita_monogami'  => ['title' => 'MONOGAMI SAHAJA', 'subtitle' => 'Hanya mempertimbangkan lelaki yang belum beristeri'],
        'wanita_terbuka'   => ['title' => 'TERBUKA (MONOGAMI / POLIGAMI)', 'subtitle' => 'Terbuka menilai kedua-duanya berdasarkan keserasian'],
        'wanita_poligami'  => ['title' => 'POLIGAMI BERPRINSIP', 'subtitle' => 'Faham dan bersedia ke arah perkahwinan poligami'],
    ];
}
