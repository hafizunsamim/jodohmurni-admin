@php
  $actionLine = match ($action) {
    'reply' => 'pentadbir telah membalas tiket helpdesk anda.',
    'resolve' => 'pentadbir telah menandakan tiket helpdesk anda sebagai selesai.',
    'reject' => 'pentadbir telah menolak tiket helpdesk anda.',
    default => 'pentadbir telah mengemas kini tiket helpdesk anda.',
  };
  $helpdeskUrl = rtrim(config('services.member_app.url', config('app.url')), '/') . '/helpdesk';
@endphp

<p>Assalamualaikum / Hello {{ $ticket->reporter_full_name }},</p>

<p>Terdapat kemas kini pada tiket helpdesk anda <strong>{{ $ticket->ticket_number }}</strong>: {{ $actionLine }}</p>

<p>Untuk melihat sejarah penuh dan sebarang mesej atau lampiran, sila layari halaman helpdesk dan gunakan carian tiket dengan <strong>nombor tiket</strong> serta <strong>emel</strong> yang sama seperti semasa anda menghantar tiket:</p>

<p><a href="{{ $helpdeskUrl }}">{{ $helpdeskUrl }}</a></p>

<p>— JodohMurni</p>
