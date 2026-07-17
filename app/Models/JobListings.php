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
public function creator()
{
    return $this->belongsTo(User::class, 'company_id', 'created_by');
}
}
