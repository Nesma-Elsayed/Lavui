<?php

namespace App\Http\Requests;

use App\Models\ProductCategory;
use App\Rules\UniqueTranslation;
use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
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
            'name_en' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'en', ProductCategory::class)
            ],
            'name_ar' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'ar', ProductCategory::class)
            ],
            'description_en' => [
                'nullable', 'string', 'max:900'
            ],
            'description_ar' => [
                'nullable', 'string', 'max:900'
            ],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'status' => ['required', 'numeric', 'in:5,10'],
        ];
    }

    protected function updateRule(): array
    {
        $ignoredId = $this->route('productCategory.id');
        return [
            'name_en' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'en', ProductCategory::class, $ignoredId)
            ],
            'name_ar' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'ar', ProductCategory::class, $ignoredId)
            ],
            'description_en' => [
                'nullable', 'string', 'max:900'
            ],
            'description_ar' => [
                'nullable', 'string', 'max:900'
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'status' => ['required', 'numeric', 'in:5,10']
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return ($this->route('productCategory.id')) ? $this->updateRule() : $this->createRule();
    }

}
