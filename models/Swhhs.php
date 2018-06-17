<?php

class Swhhs
{
    public static function createSeeAlsoRelationshipsGroup(Item $primaryItem, RelatedItemsTree $tree)
    {
        // Get the text from the See Also element and split it into a list of Ids.
        $seeAlso = ItemMetadata::getElementTextForElementName($primaryItem, 'See Also');
        $ids = array_map('trim', explode(',', $seeAlso));
        $items = array();

        foreach ($ids as $id)
        {
            // Ignoring any values that are not integers.
            $id = intval($id);
            if ($id == 0)
                continue;

            // Get the item from its Id.
            $item = self::getItemFromId($id);
            if (empty($item))
                continue;

            $items[] = $item;
        }

        return $tree->createCustomRelationshipsGroup($items, 'See Also');
    }

    protected static function getItemFromId($id)
    {
        $item = null;

        // Get the item based on its original Finding Aid Id.
        $elementId = ItemMetadata::getElementIdForElementName('Original Id');
        $items = get_records('Item', array('advanced' => array(array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $id))));

        if (empty($items))
        {
            // The Finding Aid Id didn't match an item. See if the Id is an Identifier.
            $item = ItemMetadata::getItemFromIdentifier($id);
        }
        else
        {
            $item = $items[0];
        }
        return $item;
    }
}