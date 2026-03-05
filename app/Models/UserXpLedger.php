<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserXpLedger extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'user_xp_ledger'; 

    protected $fillable = [
        'user_id',
        'source_type',
        'source_id',
        'xp_amount',
        'xp_balance_after',
        'earned_at',
    ];

    protected $casts = [
        'xp_amount'        => 'integer',
        'xp_balance_after' => 'integer',
        'earned_at'        => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}