<?php

namespace App\Http\Requests;

use App\Models\Benefit;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueTranslation;

class BenefitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function createRule(): array
    {
        return [
            'title_en'        => [
                'required', 'string', 'max:190',
                new UniqueTranslation('title', 'en', Benefit::class)
            ],
            'title_ar'        => [
                'required', 'string', 'max:190',
                new UniqueTranslation('title', 'ar', Benefit::class)
            ],
            'description_en' => [
                    'required', 'string', 'max:900',
                    new UniqueTranslation('description', 'en', Benefit::class)
                ],
            'description_ar' => [
                    'required', 'string', 'max:900',
                    new UniqueTranslation('description', 'ar', Benefit::class)
                ],
            'icon'  => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'status' => ['required', 'numeric', 'in:5,10', 'max:24'],
        ];
    }

    protected function updateRule(): array
    {
        $ignoreId = $this->route('benefit.id');

        return [
            'title_en'        => [
                'required', 'string', 'max:190',
                new UniqueTranslation('title', 'en', Benefit::class, $ignoreId)
            ],
            'title_ar'        => [
                'required', 'string', 'max:190',
                new UniqueTranslation('title', 'ar', Benefit::class, $ignoreId)
            ],
            'description_en' => [
                'required', 'string', 'max:900',
                new UniqueTranslation('description', 'en', Benefit::class, $ignoreId)
            ],
            'description_ar' => [
                'required', 'string', 'max:900',
                new UniqueTranslation('description', 'ar', Benefit::class, $ignoreId)
            ],
            'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'status' => ['required', 'numeric', 'in:5,10', 'max:24'],
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return ($this->route('benefit.id')) ? $this->updateRule() : $this->createRule();
    }
}
