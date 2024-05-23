<?php

namespace App\Http\Requests;

use App\Models\TextBanner;
use App\Rules\UniqueTranslation;
use Illuminate\Foundation\Http\FormRequest;

class TextBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function createRule(): array
    {
        return [
            'name_en' => [
                'required',
                'string',
                'max:190',
                new UniqueTranslation('name', 'en', TextBanner::class)
            ],
            'name_ar' => [
                'required',
                'string',
                'max:190',
                new UniqueTranslation('name', 'ar', TextBanner::class)
            ],
            'status' => ['required', 'numeric', 'in:5,10', 'max:24']
        ];
    }

    public function updateRule(): array
    {
        $ignoreId = $this->route('textBanner.id');

        return [
            'name_en' => [
                'required',
                'string',
                'max:190',
                new UniqueTranslation('name', 'en', TextBanner::class, $ignoreId)
            ],
            'name_ar' => [
                'required',
                'string',
                'max:190',
                new UniqueTranslation('name', 'ar', TextBanner::class, $ignoreId)
            ],
            'status' => ['required', 'numeric', 'in:5,10', 'max:24']
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ($this->route('textBanner.id')) ? $this->updateRule() : $this->createRule();
    }
}
