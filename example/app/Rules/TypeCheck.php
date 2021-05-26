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
            case 'JSONArray':
                $check = $this->isJsonArray($value);
                break;
            case 'JSONObject':
                $check = $this->isJsonObject($value);
                break;
            default:
                $check = is_string($value) ;
        }
        return $check;
    }

    private function isJsonArray($string)
    {
        $jsonObject = json_decode($string);

        if ($jsonObject === null) {
            return false;
        }

        $json = ltrim($string);

        if (strpos($json, '[') === 0) {
            return true;
        }
        return false;
    }

    private function isJsonObject($string)
    {
        $jsonObject = json_decode($string);

        if ($jsonObject === null) {
            return false;
        }

        $json = ltrim($string);

        if (strpos($json, '{') === 0) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return 'The ' . $this->attribute . ' is not ' . $this->type;
    }
}
