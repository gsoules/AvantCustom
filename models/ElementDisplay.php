<?php

class ElementDisplay
{
    public function orderElementsForDisplay($elementSetsForDisplay)
    {
        // Get the display order for the configuration options.
        $displayOrderNames = get_option('custom_elements_display_order');
        $names = explode(',', $displayOrderNames);

        // Create an array of element names in the proper order.
        $displayOrder = array();
        foreach ($names as $name)
        {
            $displayOrder[trim($name)] = null;
        }

        // Copy the elements from the element sets (Dublin Core and others) into the ordered array.
        foreach ($elementSetsForDisplay as $elementSet)
        {
            foreach ($elementSet as $elementName => $elementInfo)
            {
                $displayOrder[$elementName] = $elementInfo;
            }
        }

        // Create another array that excludes any empty elements.
        $elementSet = array();
        foreach ($displayOrder as $elementName => $elementInfo)
        {
            if (empty($elementInfo))
                continue;
            $elementSet[$elementName] = $elementInfo;
        }

        return $elementSet;
    }
}