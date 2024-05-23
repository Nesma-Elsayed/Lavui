<?php

namespace App\Http\Requests;

use App\Models\ProductSection;
use App\Rules\UniqueTranslation;
use Illuminate\Foundation\Http\FormRequest;

class ProductSectionRequest extends FormRequest
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
                new UniqueTranslation('name', 'en', ProductSection::class)

            ],
            'name_ar' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'ar', ProductSection::class)

            ],
            'title_en' => ['required', 'string', 'max:190'],
            'title_ar' => ['required', 'string', 'max:190'],
            'description_en' => ['required', 'string', 'max:190'],
            'description_ar' => ['required', 'string', 'max:190'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'status' => ['required', 'numeric', 'in:5,10', 'max:24'],
        ];
    }

    protected function updateRule(): array
    {
        $productSectionId = $this->route('productSection.id');

        return [
            'name_en' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'en', ProductSection::class, $productSectionId)
                ],
            'name_ar' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'en', ProductSection::class, $productSectionId)
                ],
            'title_en' => ['required', 'string', 'max:190',],
            'title_ar' => ['required', 'string', 'max:190',],
            'description_en' => ['required', 'string', 'max:190',],
            'description_ar' => ['required', 'string', 'max:190',],
            'image' =>  ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
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
        return ( $this->route('productSection.id')) ? $this->updateRule() : $this->createRule();
    }
}
