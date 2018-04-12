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
        return AvantCustom::getFallbackImageName($name, $args);
    }

    public function filterItemCitation($citation, $args)
    {
        return AvantCustom::getItemCitation($citation, $args);
    }

    public function filterItemThumbnailClass($class, $args)
    {
        return AvantCustom::getItemThumbnailClass($class, $args);
    }

    public function filterItemThumbnailHeader($html, $args)
    {
        return AvantCustom::getItemThumbnailHeader($html, $args);
    }

    public function hookAdminHead($args)
    {
        queue_css_file('avantcustom');
    }
}
