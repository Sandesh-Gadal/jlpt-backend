<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JlptLevel extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name_en',
        'name_ja',
        'sort_order',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function questionBanks()
    {
        return $this->hasMany(QuestionBank::class);
    }
}