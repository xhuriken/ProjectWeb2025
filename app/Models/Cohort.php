<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    protected $table        = 'cohorts';
    protected $fillable     = ['school_id', 'name', 'description', 'start_date', 'end_date'];

    /**
     * Get list of UserCohort for specific cohort_id
     * @return HasMany
     */
    public function userCohorts(): HasMany
    {
        return $this->hasMany(UserCohort::class, 'cohort_id');
    }

    /**
     * Get list of User who are link to cohort in user_cohort
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_cohorts', 'cohort_id', 'user_id');
    }

    /**
     * Return number of userCohorts (used in Cohort > index.blade.php)
     * @return int
     */
    public function usersCount(): int
    {
        return $this->userCohorts()->count();
    }

    /**
     * Return all groups for this cohort
     * @return HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
