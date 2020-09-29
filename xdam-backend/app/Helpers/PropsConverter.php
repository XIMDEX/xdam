<?php


namespace App\Helpers;

class PropsConverter
{

    // converts an entity with its properties in an array, into a classic object
    public static function arrayPropsToObjectProps($fields)
    {
        $object = new \stdClass();
        foreach ($fields as $prop => $content)
        {
            if (is_array($content))
            {
                if(count($content)==1){
                    $object->$prop = $content[0];
                }else{
                    $object->$prop = implode(",", $content);
                }
            } else {
                if (is_object($content) || is_string($content)){
                    $object->$prop = $content;
                }
            }
        }
        return $object;
    }
}
