<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    protected $table        = 'cohorts';
    protected $fillable     = ['school_id', 'name', 'description', 'start_date', 'end_date'];

    public function userCohorts(): HasMany
    {
        return $this->hasMany(UserCohort::class, 'cohort_id');
    }

    public function usersCount(): int
    {
        return $this->userCohorts()->count();
    }
}
