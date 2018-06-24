<?php

class Swhpl
{
    public static function filterDate($item, $elementId, $text)
    {
        $dateValidator = new DateValidator();
        list($year, $month, $day, $formatOk) = $dateValidator->parseDate($text);

        $formattedDate = $text;

        if ($formatOk)
        {
            // The date is valid, but it might still contain trailing text like 'circa'. Test that the parsed components
            // can be put back together to match the original text. If not, just return the text as-is.
            if (strlen($text) == 10 && $text == "$year-$month-$day")
            {
                $formattedDate = date("F j, Y", strtotime($text));
            }
            else if (strlen($text) == 7 && $text == "$year-$month")
            {
                $formattedDate = date("F Y", strtotime($text));
            }
        }

        return $formattedDate;
    }

    public static function filterPlace($item, $elementId, $text)
    {
        // Remove the "MDI, " prefix from any Place values that have it. See the AvantElements README for the
        // Implicit Link option to understand why this code must get the implicit link text fro AvantElements.
        $prefix = 'MDI, ';
        $link = AvantElements::getImplicitLink($elementId, $text);
        if (strpos($link, $prefix) !== false)
        {
            $link = str_replace($prefix, '', $link);
        }
        return $link;
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

    public static function validatePlace($item, $elementId, $text)
    {
        // Make sure Country has a value if Place has a value.
        if (!empty(AvantCommon::getPostTextForElementName('Place')))
        {
            if (empty(AvantCommon::getPostTextForElementName('Country')))
            {
                AvantElements::addError($item, 'Country', 'Country must have a value when Place has a value.');
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