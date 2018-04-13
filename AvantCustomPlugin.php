<?php

class AvantCustomPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'admin_head'
    );

    protected $_filters = array(
        'fallback_image_name',
        'item_citation',
        'item_thumbnail_class'
    );

    public function filterFallbackImageName($name, $args)
    {
        return AvantCustom::getFallbackImageName($name, $args['item']);
    }

    public function filterItemCitation($citation, $args)
    {
        return AvantCustom::getItemCitation($citation, $args['item']);
    }

    public function filterItemThumbnailClass($class, $args)
    {
        return AvantCustom::getItemThumbnailClass($class, $args['item']);
    }

    public function hookAdminHead($args)
    {
        queue_css_file('avantcustom');
    }
}
