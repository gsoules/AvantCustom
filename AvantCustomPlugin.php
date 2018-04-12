<?php

class AvantCustomPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head'
    );

    protected $_filters = array(
        'fallback_image_name',
        'item_citation',
        'item_thumbnail_class',
        'item_thumbnail_header'
    );

    public function filterFallbackImageName($name, $args)
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

    public function filterItemCitation($citation, $args)
    {
        $item = $args['item'];
        $prefix = ItemView::getIdentifierPrefix();
        $identifier = ItemView::getItemIdentifierAlias($item);
        $citation .= "<span class='citation-identifier'>{$prefix}{$identifier}</span>";
        return $citation;
    }

    public function filterItemThumbnailClass($class, $args)
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

    public function filterItemThumbnailHeader($html, $args)
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

    public function hookAdminHead($args)
    {
        queue_css_file('avantcustom');
    }
}
