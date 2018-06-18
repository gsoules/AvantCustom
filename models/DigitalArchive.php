<?php
class DigitalArchive
{
    const MAX_SUGGESTIONS = 25;

    public static function filterRights($item, $elementId, $text)
    {
        $class = 'metadata-rights-link';
        $href = '';

        switch ($text)
        {
            case __('In Copyright'):
                $href = 'http://rightsstatements.org/vocab/InC/1.0/';
                break;
            case  __('In Copyright - Educational Use Permitted'):
                $href = 'http://rightsstatements.org/vocab/InC-EDU/1.0/';
                break;
            case __('In Copyright - Non-Commercial Use Permitted'):
                $href = 'http://rightsstatements.org/vocab/InC-NC/1.0/';
                break;
            case __('In Copyright - Rights-holder(s) Unlocatable or Unidentifiable'):
                $href = 'http://rightsstatements.org/vocab/InC-RUU/1.0/';
                break;
            case __('No Copyright - United States'):
                $href = 'http://rightsstatements.org/vocab/NoC-US/1.0/';
                break;
            case __('No Copyright - Non-Commercial Use Only'):
                $href = 'http://rightsstatements.org/page/NoC-NC/1.0/';
                break;
            case __('Copyright Not Evaluated'):
                $href = 'http://rightsstatements.org/vocab/CNE/1.0/';
                break;
            case _('Copyright Undetermined'):
                $href = 'http://rightsstatements.org/vocab/UND/1.0/';
                break;
            case __('No Known Copyright'):
                $href = 'http://rightsstatements.org/vocab/NKC/1.0/';
                break;
            default:
                $href = 'http://rightsstatements.org/page/1.0';
        }

        $html = "<a href='$href' class='$class' target='_blank'>$text</a>";
        return $html;
    }

    public static function filterRightsSwhpl($item, $elementId, $text)
    {
        $class = 'metadata-rights-link';
        $href = $text;

        switch ($href)
        {
            case 'http://rightsstatements.org/vocab/InC/1.0/':
                $linkText = __('In Copyright');
                break;
            case 'http://rightsstatements.org/vocab/InC-EDU/1.0/':
                $linkText = __('In Copyright - Educational Use Permitted');
                break;
            case 'http://rightsstatements.org/vocab/InC-NC/1.0/':
                $linkText = __('In Copyright - Non-Commercial Use Permitted');
                break;
            case 'http://rightsstatements.org/vocab/InC-RUU/1.0/':
                $linkText = __('In Copyright - Rights-holder(s) Unlocatable or Unidentifiable');
                break;
            case 'http://rightsstatements.org/vocab/NoC-US/1.0/':
                $linkText = __('No Copyright - United States');
                break;
            case 'http://rightsstatements.org/vocab/CNE/1.0/':
                $linkText = __('Copyright Not Evaluated');
                break;
            case 'http://rightsstatements.org/vocab/UND/1.0/':
                $linkText = __('Copyright Undetermined');
                break;
            case 'http://rightsstatements.org/vocab/NKC/1.0/':
                $linkText = __('No Known Copyright');
                break;
            default:
                $linkText = $href;
        }

        $html = "<a href='$href' class='$class' target='_blank'>$linkText</a>";
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
        return self::getNextIdentifier();
    }

    public static function getNextIdentifier()
    {
        $identifierElementId = ItemMetadata::getIdentifierElementId();
        $db = get_db();
        $sql = "SELECT MAX(CAST(text AS SIGNED)) AS next_element_id FROM `{$db->ElementTexts}` where element_id = $identifierElementId";
        $result = $db->query($sql)->fetch();
        $id = $result['next_element_id'] + 1;
        return $id;
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
                $nextElementId = self::getNextIdentifier();
                $elementName = ItemMetadata::getElementNameFromId($elementId);
                AvantElements::addError($item, $elementName, __('%s is used by another item. Next available Identifier is %s.', $text, $nextElementId));
            }
        }
    }

    public static function validateItem($item)
    {
        $type = AvantCommon::getPostTextForElementName('Type');
        $isObjectType = substr($type, 0, 6) == 'Object';

        $subjectElementId = ItemMetadata::getElementIdForElementName('Subject');
        if (!$isObjectType && !AvantCommon::elementHasPostedValue($subjectElementId))
        {
            AvantElements::addError($item, 'Subject', __('A Subject is required except for \'Object\' types.'));
        }
    }

    public static function validateTitle($item, $elementId, $text)
    {
        if (substr($text, 0, 1) == "'")
        {
            AvantElements::addError($item, 'Title', __('A title cannot begin with a single quote. Use a double-quote instead.'));
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
                    $elementName = ItemMetadata::getElementNameFromId($elementId);
                    AvantElements::addError($item, $elementName, __('Another article exists with the same title as this article.'));
                    return;
                }
            }
        }
    }
}