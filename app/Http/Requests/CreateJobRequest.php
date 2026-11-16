<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'work_mode' => ['required', 'in:remote,on-site,hybrid'],
            'experience_level' => ['required', 'in:entry,mid,senior'],
            'location' => ['required', 'string', 'max:255'],
            'job_type' => ['required', 'in:full-time,part-time,contract,internship'],
            'salary' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'application_deadline' => ['nullable', 'date'],
            'status' => ['nullable', 'in:open,closed'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email'],
            'company_website' => ['nullable', 'string'],
            'company_logo' => ['nullable', 'string'],
            'company_description' => ['nullable', 'string'],
        ];
    }
}
