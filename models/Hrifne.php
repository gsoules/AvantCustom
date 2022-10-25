<?php

class Hrifne
{
    public static function filterPlace($item, $elementId, $text)
    {
        $separator = "<br />" . "\n";
        $places = explode($separator, $text);
        $html = "";
        foreach ($places as $place)
        {
            $placeId = preg_replace( '/[\W]/', '', strtolower($place));
            $href = "https://hrifne.avantlogic.net/digitalatlas/timeline-place.php?id=$placeId";

            if ($html)
                $html .= "<br/>";
            $html .= "<a href='$href' target='_blank'>$place</a>";
        }
        return $html;
    }
}