<?php
class DigitalArchive
{
    const MAX_SUGGESTIONS = 25;

    public static function convertRightsToUrl($text)
    {
        switch ($text)
        {
            case __('In Copyright'):
                $url = 'http://rightsstatements.org/vocab/InC/1.0/';
                break;
            case  __('In Copyright - Educational Use Permitted'):
                $url = 'http://rightsstatements.org/vocab/InC-EDU/1.0/';
                break;
            case __('In Copyright - Non-Commercial Use Permitted'):
                $url = 'http://rightsstatements.org/vocab/InC-NC/1.0/';
                break;
            case __('In Copyright - Rights-holder(s) Unlocatable or Unidentifiable'):
                $url = 'http://rightsstatements.org/vocab/InC-RUU/1.0/';
                break;
            case __('No Copyright - United States'):
                $url = 'http://rightsstatements.org/vocab/NoC-US/1.0/';
                break;
            case __('No Copyright - Non-Commercial Use Only'):
                $url = 'http://rightsstatements.org/page/NoC-NC/1.0/';
                break;
            case __('Copyright Not Evaluated'):
                $url = 'http://rightsstatements.org/vocab/CNE/1.0/';
                break;
            case _('Copyright Undetermined'):
                $url = 'http://rightsstatements.org/vocab/UND/1.0/';
                break;
            case __('No Known Copyright'):
                $url = 'http://rightsstatements.org/vocab/NKC/1.0/';
                break;
            default:
                $url = 'http://rightsstatements.org/page/1.0';
        }

        return $url;
    }

    public static function filterDate($item, $elementId, $text)
    {
        $dateValidator = new DateValidator();
        list($year, $month, $day, $formatOk) = $dateValidator->parseDate($text);

        $formattedDate = $text;

        if ($formatOk)
        {
            // The date is valid, but it might still contain trailing text like 'circa'. Test that the parsed components
            // can be put back together to match the original text. If not, just return the text as-is. Otherwise,
            // return the date pretty-printed e.g. change "1923-06-03" to "June 3, 1923".
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

    public static function filterIdentifierS3($item, $elementId, $identifier)
    {
        if (!AvantCommon::userIsAdmin())
            return $identifier;

        if (plugin_is_active('AvantS3'))
        {
            $s3Link = AvantCommon::emitS3Link($identifier);
            $identifier = "$identifier&nbsp;&nbsp;$s3Link";
        }

        if ($item->public == 0)
            $identifier = '* ' . $identifier;

        return $identifier;
    }

    public static function filterRights($item, $elementId, $text)
    {
        $class = 'metadata-rights-link';
        $href = self::convertRightsToUrl($text);
        $html = "<a href='$href' class='$class' target='_blank'>$text</a>";
        return $html;
    }

    protected static function getDateYear()
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

    public static function getDefaultIdentifier(Item $item)
    {
        return AvantCommon::getNextIdentifier();
    }

    public static function suggestTitles($item, $elementId, $text)
    {
        $titleElementId = ItemMetadata::getElementIdForElementName('Title');
        $elementSuggest = new ElementSuggest();
        return $elementSuggest->suggestElementValues($titleElementId, $text);
    }

    public static function validateIdentifier($item, $elementId, $text)
    {
        // Make sure the value is an integer.
        if (!ctype_digit($text))
        {
            AvantElements::addError($item, 'Identifier', __('Value must be a number consisting only of the digits 0 - 9.'));
            return;
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
                $nextElementId = AvantCommon::getNextIdentifier();
                $elementName = ItemMetadata::getElementNameFromId($elementId);
                AvantElements::addError($item, $elementName, __('%s is used by another item. Next available Identifier is %s.', $text, $nextElementId));
            }
        }
    }

    public static function validateItem($item)
    {
        $type = AvantCommon::getPostTextForElementName('Type');
        $isObjectType = substr($type, 0, 6) == 'Object';
        if ($isObjectType)
        {
            // Don't require a Subject when the type is object.
            return;
        }

        $subjectElementId = ItemMetadata::getElementIdForElementName('Subject');
        if (AvantCommon::elementHasPostedValue($subjectElementId))
        {
            // There is a Subject -- item is valid.
            return;
        }

        // The item type is not Object and there is no subject.
        AvantElements::addError($item, 'Subject', __('A Subject is required except for \'Object\' types or when Subject is \'none\'.'));
    }

    public static function validateTitle($item, $elementId, $text)
    {
        if (substr($text, 0, 1) == "'")
        {
            AvantElements::addError($item, 'Title', __('A title cannot begin with a single quote. Use a double-quote instead.'));
            return;
        }

        // Make sure that this item is not an Reference item with the same title as another Reference item.
        $typeValue = AvantCommon::getPostTextForElementName('Type');
        $isReference = strpos($typeValue, "Reference,") === 0;

        if ($isReference)
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
                $duplicateIsReference = strpos($duplicateType, "Reference,") === 0;
                if ($duplicateIsReference)
                {
                    $elementName = ItemMetadata::getElementNameFromId($elementId);
                    AvantElements::addError($item, $elementName, __('Another Reference item exists with the same title as this Reference item.'));
                    return;
                }
            }
        }
    }
    
    public static function validateUniqueValue($item, $elementId, $text)
    {
        // Search the database to see if another Item has this element's value.
        $items = get_records('Item', array( 'advanced' => array( array('element_id' => $elementId, 'type' => 'is exactly', 'terms' => $text ))));

        if ($items)
        {
            // Found an Item with this identifier. Check if it's the Item being saved or another Item.
            $savedItem = $item;
            $foundItem = $items[0];
            if ($savedItem->id != $foundItem->id)
            {
                $elementName = ItemMetadata::getElementNameFromId($elementId);
                AvantElements::addError($item, $elementName, __('%s is used by another item.', $text));
            }
        }
    }
}