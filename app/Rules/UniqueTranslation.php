<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueTranslation implements Rule
{
    protected $field;
    protected $locale;
    protected $ignoreId;
    protected $table;

    public function __construct($field, $locale, $model, $ignoreId = null)
    {
        $this->field = $field;
        $this->locale = $locale;
        $this->model = $model;
        $this->ignoreId = $ignoreId;
    }

    public function passes($attribute, $value)
    {
        $query = $this->model::where("{$this->field}->{$this->locale}", $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }
        return !$query->exists();
    }

    public function message()
    {
        return 'The :attribute has already been taken.';
    }
}
