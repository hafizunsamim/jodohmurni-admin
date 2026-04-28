<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAffiliateCode extends Model
{
    protected $table = 'user_affiliate_codes';

    protected $fillable = ['user_id','code','clicks'];

    protected $casts = [
        'clicks' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(Client::class, 'user_id', 'id');
    }
}
