<?php

class AvantCustom
{
    public static function getFallbackImageName($name, $item)
    {
        // Construct a file name from the item's base type and base subject.

        $itemType = ItemMetadata::getElementTextForElementName($item, 'Type');
        if (empty($itemType))
            return $name;

        $typeParts = explode(',', $itemType);
        $baseType = strtolower(trim($typeParts[0]));

        // Use the subject only with Reference items, otherwise there are too many possible file names.
        // This should suffice since most placeholders are for Reference items.
        $baseSubject = '';
        if ($baseType == 'reference')
        {
            $itemSubject = ItemMetadata::getElementTextForElementName($item, 'Subject');
            $subjectParts = explode(',', $itemSubject);
            $baseSubject = strtolower(trim($subjectParts[0]));
            if (!empty($baseSubject))
                $baseSubject = '-' . $baseSubject;
        }

        $name = "fallback-{$baseType}{$baseSubject}.png";
        return $name;
    }

    public static function getItemCitation($citation, $item)
    {
        // Append the item's Identifier to the end of the citation.

        $prefix = ItemMetadata::getIdentifierPrefix();
        $identifier = ItemMetadata::getItemIdentifierAlias($item);
        $citation .= "<span class='citation-identifier'>{$prefix}{$identifier}</span>";
        return $citation;
    }

    public static function getItemThumbnailClass($class, $item)
    {
        // Append the item's base type to it's thumbnail class. For example, if the base type
        // is 'Document, Diary' it appends 'Document'. The type portion of the class is used to
        // provide styling for the item preview thumbnail, e.g. a colored line above the image.

        $itemType = ItemMetadata::getElementTextForElementName($item, 'Type');
        if ($itemType)
        {
            // Get the base type and use it for this item's class.
            $parts = explode(',', $itemType);
            $class .= ' ' . strtolower(trim($parts[0]));
        }
        return $class;
    }
}