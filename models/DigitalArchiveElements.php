<?php
class DigitalArchiveElements
{
    public static function validateTitle($item, $elementId, $elementName, $text)
    {
        if (substr($text, 0, 1) == "'")
        {
            AvantElements::addError($item, 'Title', "A title cannot begin with a single quote. Use a double-quote instead.");
            return;
        }

        // Make sure that this item is not an article with the same title as another article.
        $typeValue = AvantCommon::getPostTextForElementName('Type');
        $isArticle = strpos($typeValue, "Article,") === 0;

        if ($isArticle)
        {
            // Get all items that have the same title.
            $duplicateItems = get_records('Item', array( 'advanced' => array( array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $text ))));
            foreach ($duplicateItems as $duplicateItem)
            {
                if ($duplicateItem->id == $item->id)
                {
                    // Ignore the item we are comparing against.
                    continue;
                }
                $duplicateType = ItemMetadata::getElementTextFromElementName($duplicateItem, array('Dublin Core', 'Type'));
                $duplicateIsArticle = strpos($duplicateType, "Article,") === 0;
                if ($duplicateIsArticle)
                {
                    AvantElements::addError($item, $elementName, "Another article exists with the same title as this article.");
                    return;
                }
            }
        }
    }
}