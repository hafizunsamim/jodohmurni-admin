<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExternalAffiliateApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $fullName;
    public string $email;
    public string $password;
    public string $loginUrl;
    public string $affiliateLink;

    public function __construct(string $fullName, string $email, string $password, string $loginUrl, string $affiliateLink)
    {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->password = $password;
        $this->loginUrl = $loginUrl;
        $this->affiliateLink = $affiliateLink;
    }

    public function build()
    {
        return $this
            ->subject('Permohonan Affiliate Pro Diluluskan')
            ->view('emails.external_affiliate_approved');
    }
}

