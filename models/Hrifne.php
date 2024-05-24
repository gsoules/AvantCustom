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
            $place_parts = explode(" : ", $place);
            $placeName = $place_parts[0];
            $placeId = $place_parts[1];

            $local = false;
            if ($local)
                $href = "http://localhost/hrifne/timeline-place.php?id=$placeId";
            else
                $href = "https://hrifne.avantlogic.net/digitalatlas/timeline-place.php?id=$placeId";

            if ($html)
                $html .= "<br/>";
            $html .= "<a href='$href' target='_blank'>$placeName</a>";
        }
        return $html;
    }
}