<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetrosElement extends Model
{
    use HasFactory;

    protected $fillable = [
        'retro_id',
        'retros_column_id',
        'title',
    ];

    public function retro()
    {
        return $this->belongsTo(Retro::class);
    }

    public function column()
    {
        return $this->belongsTo(RetrosColumn::class, 'retros_column_id');
    }
}
