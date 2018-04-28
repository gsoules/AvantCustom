<?php

class SwhplElements
{
    public static function getNextIdentifier()
    {
        return '000123';
    }

    public static function validateAccessDB($item, $accessDBValue)
    {
        $identifierElementId = ItemMetadata::getIdentifierElementId();
        $identifierValue = $_POST['Elements'][$identifierElementId][0]['text'];
        $id = (int)$identifierValue;

        if ($id == 0)
            return;

        $isAccessItem = $id >= 5000 && $id <= 12754;
        $hasAccessDBValue = !empty($accessDBValue);

        if ($isAccessItem) {
            if (!$hasAccessDBValue) {
                AvantElements::addError($item, 'Access DB', "This Access DB item must have an Access DB value of Converted or Unconverted.");
                return;
            }
        } else {
            if ($hasAccessDBValue) {
                AvantElements::addError($item, 'Access DB', "This item did not come from Access. Choose 'Select Below' for the Access DB field.");
                return;
            }
        }
    }

    public static function validateIdentifier($item, $elementId, $elementName, $text)
    {
        // Make sure the value is an integer.
        if (!ctype_digit($text))
        {
            AvantElements::addError($item, 'Identifier', 'Value must be a number consisting only of the digits 0 - 9');
            return true;
        }

        // Search the database to see if another Item has this identifier.
        $items = get_records('Item', array( 'advanced' => array( array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $text ))));

        if ($items){
            // Found an Item with this identifier. Check if it's the Item being saved or another Item.
            $savedItem = $item;
            $foundItem = $items[0];
            if ($savedItem->id != $foundItem->id) {
                $nextElementId = self::getNextIdentifier();
                AvantElements::addError($item, $elementName, "$text is used by another item. Next available Identifier is $nextElementId.");
            }
        }
        return true;
    }

    public static function validateItem(Item $item)
    {
        return;
    }

    public static function validateLocation($item, $elementId, $elementName, $text)
    {
        // Make sure Country has a value if Location has a value.
        $locationElementId = ItemMetadata::getElementIdForElementName('Location');
        if (!empty($_POST['Elements'][$locationElementId][0]['text']))
        {
            $countryElementId = ItemMetadata::getElementIdForElementName('Country');
            if (empty($_POST['Elements'][$countryElementId][0]['text']))
            {
                AvantElements::addError($item, 'Country', 'Country must have a value when Location has a value.');
            }
        }
    }

    public static function validateStatus($item, $elementId, $elementName, $text)
    {
        // Get the values of the Access DB and Status elements.
        $accessDBElementID = ItemMetadata::getElementIdForElementName('Access DB');
        $accessDBValue = $_POST['Elements'][$accessDBElementID][0]['text'];
        $statusElementId =  ItemMetadata::getElementIdForElementName('Status');
        $statusValue = $_POST['Elements'][$statusElementId][0]['text'];

        // Make sure that the Status is not set to Accepted if the Access DB field is "Unconverted".
        if ($statusValue == 'Accepted' && $accessDBValue == 'Unconverted') {
            AvantElements::addError($item, 'Status', "Status cannot be set to Accepted when Access DB is unconverted");
        }
        else {
            // Make sure that the Access DB field is not set for an item that did not come from Access.
            self::validateAccessDB($item, $accessDBValue);
        }
    }

    protected static function getItemType($item)
    {
        return metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
    }

    protected static function itemTypeIsArticle($item)
    {
        $itemType = self::getItemType($item);
        return strpos($itemType, "Article,") === 0;
    }

    public static function validateTitle($item, $elementId, $elementName, $text)
    {
        if (substr($text, 0, 1) == "'")
        {
            AvantElements::addError($item, 'Title', "A title cannot begin with a single quote. Use a double-quote instead.");
            return;
        }

        // Make sure that this item is not an article with the same title as another article.
        $typeElementId = ItemMetadata::getElementIdForElementName('Type');
        $typeValue = $_POST['Elements'][$typeElementId][0]['text'];
        $isArticle = strpos($typeValue, "Article,") === 0;

        $itemType = metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));;
        $isArticle = strpos($itemType, "Article,") === 0;

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
                if (self::itemTypeIsArticle($duplicateItem))
                {
                    AvantElements::addError($item, $elementName, "Another article exists with the same title as this article");
                    return;
                }
            }
        }
    }

}