<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class ContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'teacher';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'topic' => 'required|string|max:255',
            'grade_level' => 'required|in:10,11,12',
            'content_type' => 'required|in:video,pdf,audio,interactive,text',
            'target_learning_style' => 'required|in:visual,auditory,kinesthetic,all',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'external_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'metadata' => 'nullable|array',
        ];

        // File upload rules
        if ($this->hasFile('content_file')) {
            $rules['content_file'] = 'file|mimes:pdf,doc,docx,ppt,pptx,mp3,mp4,avi,mov|max:102400'; // 100MB max
        }

        if ($this->hasFile('thumbnail')) {
            $rules['thumbnail'] = 'image|mimes:jpg,jpeg,png,webp|max:5120'; // 5MB max
        }

        // Either file or external URL is required for new content
        if ($this->isMethod('POST')) {
            $rules[] = 'required_without_all:external_url|required_if:content_type,pdf,audio,video';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => __('validation.required', ['attribute' => __('Title')]),
            'description.required' => __('validation.required', ['attribute' => __('Description')]),
            'topic.required' => __('validation.required', ['attribute' => __('Topic')]),
            'grade_level.required' => __('validation.required', ['attribute' => __('Grade Level')]),
            'grade_level.in' => __('validation.in', ['attribute' => __('Grade Level')]),
            'content_type.required' => __('validation.required', ['attribute' => __('Content Type')]),
            'content_type.in' => __('validation.in', ['attribute' => __('Content Type')]),
            'target_learning_style.required' => __('validation.required', ['attribute' => __('Target Learning Style')]),
            'target_learning_style.in' => __('validation.in', ['attribute' => __('Target Learning Style')]),
            'difficulty_level.required' => __('validation.required', ['attribute' => __('Difficulty Level')]),
            'difficulty_level.in' => __('validation.in', ['attribute' => __('Difficulty Level')]),
            'duration_minutes.integer' => __('validation.integer', ['attribute' => __('Duration')]),
            'duration_minutes.min' => __('validation.min.numeric', ['attribute' => __('Duration'), 'min' => 1]),
            'duration_minutes.max' => __('validation.max.numeric', ['attribute' => __('Duration'), 'max' => 600]),
            'external_url.url' => __('validation.url', ['attribute' => __('External URL')]),
            'content_file.file' => __('validation.file', ['attribute' => __('Content File')]),
            'content_file.mimes' => __('validation.mimes', [
                'attribute' => __('Content File'),
                'values' => 'PDF, DOC, DOCX, PPT, PPTX, MP3, MP4, AVI, MOV'
            ]),
            'content_file.max' => __('validation.max.file', ['attribute' => __('Content File'), 'max' => 100 * 1024]),
            'thumbnail.image' => __('validation.image', ['attribute' => __('Thumbnail')]),
            'thumbnail.mimes' => __('validation.mimes', [
                'attribute' => __('Thumbnail'),
                'values' => 'JPG, JPEG, PNG, WEBP'
            ]),
            'thumbnail.max' => __('validation.max.file', ['attribute' => __('Thumbnail'), 'max' => 5 * 1024]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => __('Title'),
            'description' => __('Description'),
            'topic' => __('Topic'),
            'grade_level' => __('Grade Level'),
            'content_type' => __('Content Type'),
            'target_learning_style' => __('Target Learning Style'),
            'difficulty_level' => __('Difficulty Level'),
            'duration_minutes' => __('Duration (minutes)'),
            'external_url' => __('External URL'),
            'content_file' => __('Content File'),
            'thumbnail' => __('Thumbnail'),
            'is_active' => __('Active Status'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert duration to integer if provided
        if ($this->has('duration_minutes') && $this->duration_minutes !== null) {
            $this->merge([
                'duration_minutes' => (int) $this->duration_minutes,
            ]);
        }

        // Ensure is_active is boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
