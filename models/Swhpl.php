<?php

class Swhpl
{
    public static function filterDate($item, $elementId, $text)
    {
        $formattedDate = $text;
        $length = strlen($text);
        if ($length == 10)
        {
            $formattedDate = date("F j, Y", strtotime($text));
        }
        elseif ($length == 7)
        {
            $formattedDate = date("F Y", strtotime($text));
        }
        return $formattedDate;
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

    public static function validateLocation($item, $elementId, $text)
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

    public static function validateStatus($item, $elementId, $text)
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