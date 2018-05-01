<?php

class SwhplElements
{
    public static function getDefaultIdentifier(Item $item)
    {
        return self::getNextIdentifier();
    }

    public static function getDefaultStatus(Item $item)
    {
        return 'OK';
    }

    protected static function getNextIdentifier()
    {
        $identifierElementId = ItemMetadata::getIdentifierElementId();
        $db = get_db();
        $sql = "SELECT MAX(CAST(text AS SIGNED)) AS next_element_id FROM `{$db->ElementTexts}` where element_id = $identifierElementId";
        $result = $db->query($sql)->fetch();
        $id = $result['next_element_id'] + 1;
        return $id;
    }

    public static function saveItem($item)
    {
        return;
    }

    public static function validateAccessDB($item, $accessDBValue)
    {
        $identifierElementName = ItemMetadata::getIdentifierElementName();
        $identifierValue = AvantCommon::getPostTextForElementName($identifierElementName);
        $id = (int)$identifierValue;

        if ($id == 0)
            return;

        // SWHPL numbers in the range 5000 to 12754 came from the MS Access databae.
        $isAccessItem = $id >= 5000 && $id <= 12754;
        $hasAccessDBValue = !empty($accessDBValue);

        if ($isAccessItem)
        {
            if (!$hasAccessDBValue)
            {
                AvantElements::addError($item, 'Access DB', "This item came from MS Access. Choose Converted or Unconverted for Access DB.");
                return;
            }
        } else
            {
            if ($hasAccessDBValue)
            {
                AvantElements::addError($item, 'Access DB', "This item did not come from MS Access. Choose 'Select Below' for Access DB.");
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

        if ($items)
        {
            // Found an Item with this identifier. Check if it's the Item being saved or another Item.
            $savedItem = $item;
            $foundItem = $items[0];
            if ($savedItem->id != $foundItem->id)
            {
                $nextElementId = self::getNextIdentifier();
                AvantElements::addError($item, $elementName, "$text is used by another item. Next available Identifier is $nextElementId.");
            }
        }
        return true;
    }

    public static function validateLocation($item, $elementId, $elementName, $text)
    {
        // Make sure Country has a value if Location has a value.
        if (!empty(AvantCommon::getPostTextForElementName('Location')))
        {
            if (empty(AvantCommon::getPostTextForElementName('Country')))
            {
                AvantElements::addError($item, 'Country', 'Country must have a value when Location has a value.');
            }
        }
    }

    public static function validateStatus($item, $elementId, $elementName, $text)
    {
        // Get the values of the Access DB and Status elements.
        $accessDBValue = AvantCommon::getPostTextForElementName('Access DB');
        $statusValue = AvantCommon::getPostTextForElementName('Status');

        // Make sure that the Status is not set to Accepted if the Access DB field is "Unconverted".
        if ($statusValue == 'Accepted' && $accessDBValue == 'Unconverted') {
            AvantElements::addError($item, 'Status', "Status cannot be set to Accepted when Access DB is unconverted.");
        }
        else {
            // Make sure that the Access DB field is not set for an item that did not come from Access.
            self::validateAccessDB($item, $accessDBValue);
        }
    }
}