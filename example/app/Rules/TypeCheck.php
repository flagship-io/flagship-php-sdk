<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TypeCheck implements Rule
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $attribute;

    /**
     * TypeCheck constructor.
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        $check = false;
        switch ($this->type) {
            case 'bool':
                $check = null !== filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
            case 'double':
            case 'long':
            case 'float':
            case 'int':
            case 'integer':
                $check = is_numeric($value);
                break;
            default:
                $check = is_string($value) ;
        }
        return $check;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'The ' . $this->attribute . ' is not ' . $this->type;
    }
}
