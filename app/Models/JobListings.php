<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobListings extends Model
{   protected $table = 'joblistings';

     protected $fillable = [
        'company_id',
        'created_by',
        'title',
        'category',
        'description',
        'location',
        'work_mode',
        'job_type',
        'experience_level',
        'salary',
        'requirements',
        'responsibilities',
        'benefits',
        'application_deadline',
        'status'
    ];

    /**
     * The company that posted this job listing.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
