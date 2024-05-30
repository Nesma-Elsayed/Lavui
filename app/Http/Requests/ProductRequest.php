<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Rules\IniAmount;
use App\Rules\UniqueTranslation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
                new UniqueTranslation('name', 'en', Product::class)
            ],
           'name_ar' => [
               'required', 'string', 'max:190',
               new UniqueTranslation('name', 'ar', Product::class)
           ],
           'sku' => [
               'required',
               'numeric',
               'max_digits:7',
               Rule::unique("products", "sku")->whereNull('deleted_at')->ignore($this->route('product.id'))
           ],
           'product_category_id'        => ['required', 'numeric', 'exists:product_categories,id', 'not_in:0'],
           'text_banner_id'             => ['required', 'numeric', 'exists:text_banners,id', 'not_in:0'],
           'group_id'                   => ['required', 'numeric', 'exists:groups,id', 'not_in:0'],
           'barcode_id'                 => ['required', 'numeric', 'not_in:0', 'exists:barcodes,id'],
           'buying_price'               => ['required', new IniAmount()],
           'selling_price'              => ['required', new IniAmount()],
           'tax_id'                     => ['required', 'numeric', 'max_digits:10', 'exists:taxes,id'],
           'product_brand_id'           => ['required', 'numeric', 'max_digits:10', 'exists:product_brands,id'],
           'status'                     => ['required', 'numeric', 'in:5,10', 'max:24'],
           'can_purchasable'            => ['required', 'numeric', 'max:24', 'in:5,10'],
           'show_stock_out'             => ['required', 'numeric', 'max:24', 'in:5,10'],
           'refundable'                 => ['required', 'numeric', 'max:24', 'in:5,10'],
           'maximum_purchase_quantity'  => ['required', 'numeric', 'max_digits:10'],
           'low_stock_quantity_warning' => ['required', 'numeric', 'max_digits:10'],
           'unit_id'                    => ['required', 'numeric', 'not_in:0', 'exists:units,id'],
           'description_en'             => ['required', 'string', 'max:5000'],
           'description_ar'             => ['required', 'string', 'max:5000'],
           'tags'                       => ['nullable', 'array'],
           'image_featured'             => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
           'new_arrival_image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
           'product_trend_image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
           'product_story_image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
           'size'                       => ['nullable', 'numeric', 'gt:0'],
           'arm'                        => ['nullable', 'numeric', 'gt:0'],
           'bridge'                     => ['nullable', 'numeric', 'gt:0'],
           'double_price_for_two_lens'  => ['nullable', new IniAmount()],
        ];
    }

    protected function updateRule(): array
    {
        $ignoreId = $this->route('product.id');
        return [
            'name_en' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'en', Product::class, $ignoreId)
            ],
            'name_ar' => [
                'required', 'string', 'max:190',
                new UniqueTranslation('name', 'ar', Product::class, $ignoreId)
            ],
            'sku' => [
                'required',
                'numeric',
                'max_digits:7',
                Rule::unique("products", "sku")->whereNull('deleted_at')->ignore($this->route('product.id'))
            ],
            'product_category_id'        => ['required', 'numeric', 'exists:product_categories,id', 'not_in:0'],
            'text_banner_id'             => ['required', 'numeric', 'exists:text_banners,id', 'not_in:0'],
            'group_id'                   => ['required', 'numeric', 'exists:groups,id', 'not_in:0'],
            'barcode_id'                 => ['required', 'numeric', 'not_in:0', 'exists:barcodes,id'],
            'buying_price'               => ['required', new IniAmount()],
            'selling_price'              => ['required', new IniAmount()],
            'tax_id'                     => ['nullable', 'numeric', 'max_digits:10', 'exists:taxes,id'],
            'product_brand_id'           => ['required', 'numeric', 'max_digits:10', 'exists:product_brands,id'],
            'status'                     => ['required', 'numeric', 'in:5,10','max:24'],
            'can_purchasable'            => ['required', 'numeric', 'max:24', 'in:5,10'],
            'show_stock_out'             => ['required', 'numeric', 'max:24', 'in:5,10'],
            'refundable'                 => ['required', 'numeric', 'max:24', 'in:5,10'],
            'maximum_purchase_quantity'  => ['required', 'numeric', 'max_digits:10'],
            'low_stock_quantity_warning' => ['required', 'numeric', 'max_digits:10'],
            'unit_id'                    => ['required', 'numeric', 'not_in:0', 'exists:units,id'],
            'description_en'             => ['required', 'string', 'max:5000'],
            'description_ar'             => ['required', 'string', 'max:5000'],
            'tags'                       => ['nullable', 'array'],
            'image_featured'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'new_arrival_image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'product_trend_image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'product_story_image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'size'                       => ['nullable', 'numeric', 'gt:0'],
            'arm'                        => ['nullable', 'numeric', 'gt:0'],
            'bridge'                     => ['nullable', 'numeric', 'gt:0'],
            'double_price_for_two_lens'  => ['nullable', new IniAmount()],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return ($this->route('product.id')) ? $this->updateRule() : $this->createRule();

    }

    public function attributes(): array
    {
        return [
            'product_category_id' => strtolower(trans('all.label.product_category_id')),
            'product_brand_id'    => strtolower(trans('all.label.product_brand_id')),
            'text_banner_id'      => strtolower(trans('all.label.text_banner_id')),
            'barcode_id'          => strtolower(trans('all.label.barcode_id')),
            'group_id'            => strtolower(trans('all.label.group_id')),
            'unit_id'             => strtolower(trans('all.label.unit_id')),
            'tax_id'              => strtolower(trans('all.label.tax_id')),
        ];
    }

//    public function withValidator($validator): void
//    {
//        $validator->after(function ($validator) {
//            $sku = ProductVariation::where('sku', $this->sku)->first();
//            if ($sku) {
//                $validator->getMessageBag()->add('sku', trans('all.message.sku_exist'));
//            }
//        });
//    }
}
