<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPillar extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'description',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
