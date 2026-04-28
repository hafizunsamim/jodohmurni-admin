<div style="font-family: Arial, sans-serif; line-height: 1.5;">
  <p>Assalamualaikum {{ $fullName }},</p>

  <p>Permohonan anda untuk menjadi <strong>Affiliate Pro</strong> telah <strong>diluluskan</strong>.</p>

  <div style="padding:12px; border:1px solid #e5e7eb; border-radius:10px; background:#fafafa;">
    <p style="margin:0 0 8px;"><strong>Maklumat Login</strong></p>
    <p style="margin:0;">Email: <strong>{{ $email }}</strong></p>
    <p style="margin:0;">Password sementara: <strong>{{ $password }}</strong></p>
    <p style="margin:8px 0 0;">Login URL: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
  </div>

  <p style="margin-top:16px;"><strong>Link Affiliate anda</strong></p>
  <p style="margin-top:0;">
    <a href="{{ $affiliateLink }}">{{ $affiliateLink }}</a>
  </p>

  <p>Anda boleh mula kongsikan link affiliate ini untuk menjana komisen.</p>

  <p>Terima kasih,<br>Team JodohMurni</p>
</div>

