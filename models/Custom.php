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
        $dirName = self::getZoomDataDirName($identifier);
        $pathName = self::getZoomDataPath($identifier) . $identifier . '/';

        $xmlFileName = $dirName . DIRECTORY_SEPARATOR . 'ImageProperties.xml';
        if (file_exists($xmlFileName))
        {
            $sources[] = self::getZoomDataProperties($dirName, $pathName);
        }
        else
        {
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