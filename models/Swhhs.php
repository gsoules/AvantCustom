<?php

class Swhhs
{
    public static function createSeeAlsoRelationshipNodes(Item $item, RelatedItemsTree $tree)
    {
        $label = 'See Also';
        $alsos = ItemMetadata::getElementTextForElementName($item, 'See Also');

        $treeNode = new RelatedItemsTreeNode(0, $label);

        $references = array_map('trim', explode(',', $alsos));

        foreach ($references as $reference)
        {
            // Get the Id, ignoring any text that's in the See Also element.
            $id = intval($reference);
            if ($id == 0)
                continue;

            // Get the item from the Finding Aid Id. If that doesn't work, check to see if its an Identifier.
            $item = self::getItemFromFindingAidId($id);
            if (empty($item))
            {
                $item = ItemMetadata::getItemFromIdentifier($id);
            }
            if (empty($item))
                continue;

            // Create a related item for the See Also Id.
            $relatedItemId = $item->id;
            $relatedItem = ItemMetadata::getItemFromId($relatedItemId);
            if (empty($relatedItem))
                continue;

            // Add the related item to the See Also tree node.
            $tree->addKidToRelatedItemsTreeNode($item, $item->id, $label, $treeNode);
        }

        return array($treeNode);
    }

    protected static function getItemFromFindingAidId($id)
    {
        $elementId = ItemMetadata::getElementIdForElementName('Original Id');
        $items = get_records('Item', array('advanced' => array(array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $id))));
        if (empty($items))
            return null;
        return $items[0];
    }
}