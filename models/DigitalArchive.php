<?php
class DigitalArchive
{
    protected static function getDefaultDateYear()
    {
        $dateValidator = new DateValidator();
        $dateText = AvantCommon::getPostTextForElementName('Date');
        if (strlen($dateText) == 0)
        {
            return '';
        }
        list($dateYear, $month, $day, $formatOk) = $dateValidator->parseDate($dateText);
        if (!$formatOk)
        {
            return '';
        }
        return $dateYear;
    }

    public static function getDefaultStartEndYear(Item $item, $elementId)
    {
        // Return the year from the Date field and also insert the year into the post as though the user had entered it.
        // The returned value is for Omeka's save logic. Inserting into the post avoids the beforeSave validation error
        // that occurs when Date is set, but the start/end years are blank. Note that this method only gets called when
        // Date Start or Date End are blank. As such, it won't handle the case where the start/end years are already set
        // and then the user changes the Date year. In that case a date error will occur due to the year mismatch.
        // However, this handles the most common case where a user is simply adding a Date value to an item.
        $year = self::getDefaultDateYear();
        AvantCommon::setPostTextForElementId($elementId, $year);
        return $year;
    }

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
                $duplicateType = ItemMetadata::getElementTextForElementName($duplicateItem, 'Type');
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