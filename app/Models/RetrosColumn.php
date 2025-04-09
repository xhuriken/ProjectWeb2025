<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetrosColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'retro_id',
        'title',
    ];

    public function retro()
    {
        return $this->belongsTo(Retro::class);
    }

    public function elements()
    {
        return $this->hasMany(RetrosElement::class, 'retros_column_id');
    }
}
