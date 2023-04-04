<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckBoolean implements Rule
{
    /**
     * @var string
     */
    private $attribute;

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        return null !== filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'The ' . $this->attribute . ' is not ' . 'bool';
    }
}
