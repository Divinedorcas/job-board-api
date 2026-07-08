<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\JobController;

class CreateJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'work_mode' => ['required', 'in:remote,on-site,hybrid'],
            'experience_level' => ['required', 'in:entry,mid,senior'],
            'location' => ['required', 'string', 'max:255'],
            'job_type' => ['required', 'in:full-time,part-time,contract,internship'],
            'salary' => ['nullable' , 'string', 'max:255'],
            'application_deadline' => ['nullable', 'date'],
            'status' => ['required', 'in:open,closed'],
        ];
    }
}

