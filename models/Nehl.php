<?php
class Nehl
{
    public static function requiredUnlessReferenceType($item, $elementId, $text)
    {
        // Make the Object ID and Location elements required except when the item Type is Reference.
        // This is to deal with the fact that Reference items have no physical location and don't need an Object ID.

        $typeValue = AvantCommon::getPostTextForElementName('Type');
        if ($typeValue == 'Reference')
        {
            return;
        }

        $objectIdValue = AvantCommon::getPostTextForElementName('Object ID');
        $locationValue = AvantCommon::getPostTextForElementName('Location');

        if (strlen($objectIdValue) == 0)
        {
            AvantElements::addError($item, 'Object ID', 'A value is required except when the item Type is Reference.');
        }

        if (strlen($locationValue) == 0)
        {
            AvantElements::addError($item, 'Location', 'A value is required except when the item Type is Reference.');
        }
    }
}