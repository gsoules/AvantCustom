<?php

class AvantCustom
{
    public static function getFallbackImageName($name, $args)
    {
        $item = $args['item'];

        if (is_admin_theme() || $item == null)
            return $name;

        $itemType = metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
        if (empty($itemType))
            return $name;

        $typeParts = explode(',', $itemType);
        $type = strtolower(trim($typeParts[0]));

        // Use the subject only with articles, otherwise there are too many possible file names.
        // This should suffice since most placeholders are for articles.
        $subject = '';
        if ($type == 'article')
        {
            $itemSubject = metadata($item, array('Dublin Core', 'Subject'), array('no_filter' => true));
            $subjectParts = explode(',', $itemSubject);
            $subject = strtolower(trim($subjectParts[0]));
            if (!empty($subject))
                $subject = '-' . $subject;
        }

        $name = "fallback-{$type}{$subject}.png";

        return $name;
    }

    public static function getItemCitation($citation, $args)
    {
        $item = $args['item'];
        $prefix = ItemView::getIdentifierPrefix();
        $identifier = ItemView::getItemIdentifierAlias($item);
        $citation .= "<span class='citation-identifier'>{$prefix}{$identifier}</span>";
        return $citation;
    }

    public static function getItemThumbnailClass($class, $args)
    {
        $item = $args['item'];
        $itemType = metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
        if ($itemType)
        {
            // Get the base type and use it for this item's class.
            $parts = explode(',', $itemType);
            $class .= ' ' . strtolower(trim($parts[0]));
        }
        return $class;
    }

    public static function getItemThumbnailHeader($html, $args)
    {
        $item = $args['item'];
        $identifier = ItemView::getItemIdentifierAlias($item);
        $prefx = ItemView::getIdentifierPrefix();
        if ($item->public == 0)
            $identifier .= '*';

        $url = url("items/show/{$item->id}");

        $html = '<div class="item-preview-header">';
        $html .= "<a class='item-preview-identifier' href=\"$url\">{$prefx}{$identifier}</a>";
        $html .= '</div>';
        return $html;
    }
}