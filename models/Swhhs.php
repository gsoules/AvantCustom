<?php

class Swhhs
{
    public static function createSeeAlsoRelationshipNodes(RelatedItemsTree $tree)
    {
        $label = 'See Also';
        $relatedItemId = '188';
        $relatedItem = ItemMetadata::getItemFromId($relatedItemId);
        if (empty($relatedItem))
            return array();
        $customRelationshipsNode = $tree->createCustomRelationshipsNode($relatedItem, $label);
        return array($customRelationshipsNode);
    }
}