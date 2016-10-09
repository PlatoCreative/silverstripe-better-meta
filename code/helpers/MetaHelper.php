<?php
/**
 * Provides markup for schema tags
 *
 * @package silverstripe
 * @subpackage mysite
 */
class MetaHelper
{
    /**
     * @param array $array
     */
    public static function SchemaTag($array, $type = 'application/ld+json')
    {
        if (empty($array)) {
            return false;
        }
        $json = Convert::array2json($array);
        return "<script type='{$type}'>{$json}</script>";
    }

    /**
     * @param string $name
     * @param mix $array
     * @param string $separator
     */
    public static function MetaTag($name, $content, $separator = ', ')
    {
        if (is_array($content)) {
            $content = implode($separator, $robots);
        }
        $content = Convert::raw2att($content);
        return "<meta name='{$name}' content='{$content}' />";
    }
}
