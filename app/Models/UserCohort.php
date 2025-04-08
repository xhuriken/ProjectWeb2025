<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCohort extends Model
{
    protected $table        = 'users_cohorts';
    protected $fillable     = ['id', 'user_id', 'cohort_id', 'active'];

}
