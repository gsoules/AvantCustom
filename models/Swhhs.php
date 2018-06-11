<?php

class Swhhs
{
    public static function createSeeAlsoRelationshipNodes(Item $item, RelatedItemsTree $tree)
    {
        $nodes = array();
        $label = 'See Also';
        $alsos = ItemMetadata::getElementTextForElementName($item, 'See Also');

        $references = array_map('trim', explode(',', $alsos));

        foreach ($references as $reference)
        {
            $id = intval($reference);
            if ($id == 0)
                continue;
            $item = self::getItemFromFindingAidIs($id);
            if (empty($item))
                continue;

            $relatedItemId = $item->id;
            $relatedItem = ItemMetadata::getItemFromId($relatedItemId);
            if (empty($relatedItem))
                continue;
            $nodes[] = $tree->createCustomRelationshipsNode($relatedItem, $label);
        }

        return $nodes;
    }

    protected static function getItemFromFindingAidIs($id)
    {
        $elementId = ItemMetadata::getElementIdForElementName('Original Id');
        $items = get_records('Item', array('advanced' => array(array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $id))));
        if (empty($items))
            return null;
        return $items[0];
    }
}