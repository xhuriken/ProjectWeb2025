<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retro extends Model
{
    use HasFactory;

    protected $fillable = [
        'cohort_id',
        'title',
    ];

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function columns()
    {
        return $this->hasMany(RetrosColumn::class);
    }

    public function elements()
    {
        return $this->hasMany(RetrosElement::class);
    }
}
