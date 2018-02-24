<?php

class Custom
{
    public static function emitSearchForm()
    {
        $url = url('find');

        $form =
            '<form id="search-form" name="search-form" action="' . $url. '" method="get">
            <input type="text" name="query" id="query" value="" title="Search">
            <button id="submit_search" type="submit" value="Search">Search</button></form>';

        echo $form;
    }

    public static function emitZoomScript($identifier, $zoomDataProperties)
    {
        $zoomDataPath = Custom::getZoomDataPath($identifier);

        $tileSources = '[';
        foreach ($zoomDataProperties as $property)
        {
            $tileSources .= '{' . PHP_EOL;
            $tileSources .= 'type: "zoomifytileservice",' . PHP_EOL;
            $tileSources .= 'width: ' . $property['width'] . ',' . PHP_EOL;
            $tileSources .= 'height: ' . $property['height'] . ',' . PHP_EOL;
            $tileSources .= 'tilesUrl: "' . $property['url'] . '"' . PHP_EOL;
            $tileSources .= '},' . PHP_EOL;

        }
        $tileSources .= ']';

        $collectionOption = '';
        $tileSourceCount = count($zoomDataProperties);
        if ($tileSourceCount >= 2)
        {
            $rows = 1;
            if ($tileSourceCount >= 4)
                $rows = 2;
            if ($tileSourceCount > 8)
                $rows = 3;
            $collectionOption .= 'sequenceMode: false,' . PHP_EOL;
            $collectionOption .= 'collectionMode: true,' . PHP_EOL;
            $collectionOption .= 'collectionRows: ' . $rows . ',' . PHP_EOL;
            $collectionOption .= 'collectionTileSize: 1024,' . PHP_EOL;
            $collectionOption .= 'collectionTileMargin: 256,' . PHP_EOL;
        }

        $script = '
            var viewer = OpenSeadragon({
            //debugMode: true,
            id: "openseadragon",
            showNavigator: true,
            prefixUrl: "' . $zoomDataPath . 'images/",' .
            $collectionOption . '
            tileSources: ' . $tileSources . '
            })';

        return $script;
    }

    public static function getCustomItemTypeId()
    {
        // In a standard Omeka implementation the admin choose the item type for an item from a list. They choose it when
        // they create a new item and whenever they edit an item's type-specific (non Dublin Core) elements. That's an
        // extra step for the admin, but allows them to use different item types. With AvantCustom, there is only one item
        // type. The admin adds it as part of their initial Omeka setup and specifies its name on the AvantCustom configuration
        // page. This function gets called by the logic that would normally operate on the admin's selection from the Item Types
        // list. Note that the admin could delete all but their one custom item type, but this function assumes that there
        // are others that Omeka automatically installed. It finds the right one and returns its ID.

        $itemTypes = get_db()->getTable('ItemType')->findAll();
        $customItemTypeName = get_option('custom_item_type_name');

        // Use the first item type as the default in case the user specified an invalid name in the configuration options.
        $customItemTypeId = $itemTypes[0]->id;

        foreach ($itemTypes as $itemType)
        {
            if ($itemType->name == $customItemTypeName)
            {
                $customItemTypeId = $itemType->id;
                break;
            }
        }

        return $customItemTypeId;
    }

    public static function getItemBaseType($item)
    {
        $type = '';
        $itemType = metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
        if ($itemType)
        {
            $parts = explode(',', $itemType);
            $type = strtolower(trim($parts[0]));
        }
        return $type;
    }

    public static function getZoomDataDirName($identifier)
    {
        return FILES_DIR . DIRECTORY_SEPARATOR . 'zoom' . DIRECTORY_SEPARATOR . $identifier;
    }

    public static function getZoomDataPath($identifier)
    {
        $currentPagePath = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/');
        return "/$currentPagePath/files/zoom/";
    }

    public static function getZoomDataProperties($dirName, $url)
    {
        $xmlFileName = $dirName . DIRECTORY_SEPARATOR . 'ImageProperties.xml';
        $xml = simplexml_load_file($xmlFileName);

        if ($xml)
        {
            $width = (string)$xml[0]['WIDTH'];
            $height = (string)$xml[0]['HEIGHT'];
            return array('url' => $url, 'width' => $width, 'height' => $height);
        }
        return null;
    }

    public static function getZoomDataSources($identifier)
    {
        $sources = array();
        if (empty($identifier))
            return $sources;

        $dirName = self::getZoomDataDirName($identifier);
        $pathName = self::getZoomDataPath($identifier) . $identifier . '/';

        $xmlFileName = $dirName . DIRECTORY_SEPARATOR . 'ImageProperties.xml';
        if (file_exists($xmlFileName))
        {
            // There is a single folder of tiles for one image.
            $sources[] = self::getZoomDataProperties($dirName, $pathName);
        }
        else
        {
            // There is a folder of folders of tiles containing multiple images for a single item.
            $dirs = glob($dirName . DIRECTORY_SEPARATOR . '*');

            foreach ($dirs as $dirName)
            {
                if (is_dir($dirName))
                {
                    $properties = self::getZoomDataProperties($dirName, $pathName . basename($dirName) . '/');
                    if ($properties)
                        $sources[] = $properties;
                }
            }
        }

        return $sources;
    }

    public static function removeDirectory($path)
    {
        $files = glob($path . '/*');
        foreach ($files as $file)
        {
            is_dir($file) ? self::removeDirectory($file) : unlink($file);
        }
        rmdir($path);
    }
}