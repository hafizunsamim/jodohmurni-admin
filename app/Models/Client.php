<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    // guna table users
    protected $table = 'users';

    // primary key char(36)
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // kalau table users ada created_at/updated_at (ya, ada)
    public $timestamps = true;

    protected $fillable = [
        'id',
        'name',
        'nickname',
        'status_keahlian',
        'affiliate_pro_approved_at',
        'is_external_affiliate',
        'email',
        'phone',
        'password',
        'country',
        'state',
        'district',
        'gender',
        'marital_status',
        'path',
        'poligami_situation',
        'poligami_level',
        'photo_1',
        'photo_2',
        'photo_3',
        'photo_3_hobby_tag',
        'photo_4',
        'date_of_birth',
        'email_verified_at',
        'remember_token',
        'role',
        'occupation_type',
        'government_level',
        'government_uniform',
        'government_role_level',
        'private_role',
        'business_scale',
        'hobbies',
        'social_activities',
        'education_level',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'affiliate_pro_approved_at' => 'datetime',
        'is_external_affiliate' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // scope: hanya user biasa (bukan admin)
    public function scopeOnlyUsers($q)
    {
        return $q->where('role', 'user');
    }
}