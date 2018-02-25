<?php

class AvantCustomPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'after_delete_record',
        'admin_head',
        'admin_items_panel_buttons',
        'admin_items_show',
        'admin_items_show_sidebar',
        'before_save_item',
        'config',
        'config_form',
        'define_routes',
        'initialize',
        'install',
        'search_sql'
    );

    protected $_filters = array(
        'admin_items_form_tabs',
        'admin_navigation_main',
        'custom_relationships',
        'fallback_image_name',
        'file_markup',
        'item_citation',
        'public_show_admin_bar',
        'item_thumbnail_class',
        'item_thumbnail_header'
    );

    protected function createCustomRelationshipsFor($item, RelatedItemsTree $tree, $elementName, $groupName)
    {
        $elementId = ElementFinder::getElementIdForElementName($elementName);
        $title = ItemView::getItemTitle($item, false);
        $label = $groupName;

        $results = ElementFinder::getItemsWithElementValue($elementId, $title);

        if (empty($results))
            return null;

        $customRelationshipsNode = new RelatedItemsTreeNode(0, $label);

        // Form a URL for a search that will find all the related items. The URL is
        // emitted in the "See all n items" link that appears following a short list of items.
        $url = ElementFinder::getAdvancedSearchUrl($elementId, $title);
        $imageViewId = SearchResultsViewFactory::IMAGE_VIEW_ID;
        $url .= "&view=$imageViewId";
        $customRelationshipsNode->setData($url);

        foreach ($results as $result)
        {
            $itemId = $result['id'];
            if (RelatedItemsTree::containsItem($itemId, $tree->getRootNode()))
            {
                // This item is part of another relationship so don't emit it again.
                // If it's the only custom item, then don't emit the custom tree.
                if (count($results) == 1)
                    return null;
                else
                    continue;
            }
            $item = ItemView::getItemFromId($itemId);
            if (empty($item))
            {
                // The user does not have access to the target item e.g. because it's private.
                continue;
            }
            $itemTitle = ItemView::getItemTitle($item);
            $relatedItem = new RelatedItem($itemId);
            $relatedItem->setItem($item);
            $relatedItem->setLabels($label);
            $kid = new RelatedItemsTreeNode($itemId, $itemTitle, $relatedItem);
            $customRelationshipsNode->addKid($kid);
        }

        return $customRelationshipsNode;
    }

    protected function emitLightboxLink($item, $identifier, $useCoverImage)
    {
        $html = '';
        $url = ItemView::getImageUrl($item, $useCoverImage);
        if (!empty($url))
        {
            $title = __('Item ') . $identifier . ' - ' . ItemView::getItemTitle($item);
            $html = "<a class='lightbox' href='$url' title='$title'></a>";
        }
        return $html;
    }

    public function filterAdminItemsFormTabs($tabs, $args)
    {
        // Display a custom name for the "Item Type Metadata' tab on the admin/edit page.
        // If the administrator did not configure a name, use the default name.
        $newTabs = array();
        foreach ($tabs as $key => $tab) {
            if ($key == 'Item Type Metadata') {
                $tabName = get_option('custom_item_type_name');
                if (!$tabName)
                    $tabName = $key;
            }
            else {
                $tabName = $key;
            }
            $newTabs[$tabName] = $tab;
        }
        return $newTabs;
    }

    public function filterAdminNavigationMain($nav)
    {
        // Remove 'Collections' from the admin left menu panel.
        $key = array_search('Collections', array_column($nav, 'label'));
        if ($key)
            unset($nav[$key]);

        return $nav;
    }

    public function filterCustomRelationships($nodes, $args)
    {
        $item = $args['item'];
        $tree = $args['tree'];

        $node = $this->createCustomRelationshipsFor($item, $tree, 'Creator', 'Created');
        if (!empty($node))
            $nodes[] = $node;

        $node = $this->createCustomRelationshipsFor($item, $tree, 'Publisher', 'Published');
        if (!empty($node))
            $nodes[] = $node;

        return $nodes;
    }

    public function filterFallbackImageName($name, $args)
    {
        $item = $args['item'];

        if (is_admin_theme() || $item == null)
            return $name;

        $itemType = metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
        if (empty($itemType))
            return $name;

        $typeParts = explode(',', $itemType);
        $type = strtolower(trim($typeParts[0]));

        // Use the subject only with articles, otherwise there are too many possible file names.
        // This should suffice since most placeholders are for articles.
        $subject = '';
        if ($type == 'article')
        {
            $itemSubject = metadata($item, array('Dublin Core', 'Subject'), array('no_filter' => true));
            $subjectParts = explode(',', $itemSubject);
            $subject = strtolower(trim($subjectParts[0]));
            if (!empty($subject))
                $subject = '-' . $subject;
        }

        $name = "fallback-{$type}{$subject}.png";

        return $name;
    }

    public function filterFileMarkup($html, $args)
    {
        // Not using -- can remove this filter.
        return $html;
    }

    public function filterItemCitation($citation, $args)
    {
        $item = $args['item'];
        $identifier = ItemView::getItemIdentifier($item);
        $citation .= "<span class='citation-identifier'>Item $identifier</span>";
        return $citation;
    }

    public function filterItemThumbnailClass($class, $args)
    {
        $item = $args['item'];
        $itemType = metadata($item, array('Dublin Core', 'Type'), array('no_filter' => true));
        if ($itemType)
        {
            // Get the base type and use it for this item's class.
            $parts = explode(',', $itemType);
            $class .= ' ' . strtolower(trim($parts[0]));
        }
        return $class;
    }

    public function filterItemThumbnailHeader($html, $args)
    {
        $item = $args['item'];
        $useCoverImage = $args['use_cover_image'];
        $identifier = ItemView::getItemIdentifier($item);
        if ($item->public == 0)
           $identifier .= '*';
        $html = '<div class="item-preview-header">';
        $html .= $this->emitLightboxLink($item, $identifier, $useCoverImage);
        $html .= "<span class=\"related-item-identifier\">Item $identifier</span>";
        $html .= '</div>';
        return $html;
    }

    public function filterPublicShowAdminBar($show)
    {
        // Don't show the admin bar unless a user is logged in and they are not a researcher.
        $user = current_user();

        if (empty($user))
            return false;

        if ($user->role == 'researcher')
            return false;

        return true;
    }

    public static function getDate($date)
    {
        $date = new DateTime($date);
        $date->setTimezone(new DateTimeZone("America/New_York"));
        return $date->format('Y-n-j, g:i a');
    }

    protected function getRedirector()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    }

    public function hookAfterDeleteRecord($args)
    {
        $item = $args['record'];

        // This code is only for Item objects, but it gets called when other kinds of records get deleted
        // such as an item's search_text table record. Ignore those other objects.
        if (!($item instanceof Item))
            return;

        $identifier = ItemView::getItemIdentifier($item);
        $zoomDataDirName = Custom::getZoomDataDirName($identifier);
        if (file_exists($zoomDataDirName))
        {
            Custom::removeDirectory($zoomDataDirName);
        }
    }

    public function hookAdminHead($args)
    {
        queue_css_file('custom');
    }

    public function hookAdminItemsPanelButtons($args)
    {
        // Add a 'Cancel' button on the admin right button panel. It appears when editing an existing
        // item or adding a new item. When editing, pressing the Cancel button takes the user back to
        // the Show page for the item. When adding a new item, it takes them to the Dashboard.
        $itemId = $args['record']->id;
        $url = $itemId ? 'items/show/' . $itemId : '.';
        echo '<a href=' . html_escape(admin_url($url)) . ' class="big blue button">' . __('Cancel') . '</a>';
    }

    public function hookAdminItemsShow($args)
    {
        // Not currently being used.
        // HTML emitted here will appear after the last metadata element but before related items.
    }

    public function hookAdminItemsShowSidebar($args)
    {
        $this->showItemHistory($args['item']);
    }

    public function hookBeforeSaveItem($args)
    {
        $item = $args['record'];
        self::setItemType($item);
    }

    public function hookConfig()
    {
        set_option('custom_item_type_name', $_POST['custom_item_type_name']);
        set_option('custom_maintenance', (int)(boolean)$_POST['custom_maintenance']);
        set_option('custom_elements_display_order', $_POST['custom_elements_display_order']);
    }

    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
    }

    public function hookInstall()
    {
        return;
    }

    public function hookInitialize()
    {
        // Register the dispatch filter controller plugin.
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new AvantCustom_Controller_Plugin_DispatchFilter);
    }

    public function hookSearchSql($args)
    {
        $params = $args['params'];

        $isAdmin = isset($params['admin']) && $params['admin'] == true;
        $isSearch = isset($params['controller']) ? $params['controller'] == 'search' : false;
        $query = isset($params['query']) ? $params['query'] : null;

        $isAdminSimpleSearch = $isAdmin && $isSearch && !empty($query);

        if ($isAdminSimpleSearch && is_numeric($query))
        {
            // The user typed a number in the admin simple search text box. If the number is a valid item, Identifier
            // show the item instead of displaying search results. Note that this hook is here in the AvantCustom plugin
            // instead of in the AvantSearch plugin, because this is a custom feature.
            $id = ItemView::getItemIdFromIdentifier($query);
            if ($id)
            {
                $this->redirectToShowPageForItem($id);
            }
        }
    }

    protected function redirectToShowPageForItem($id)
    {
        // Construct the URL for the 'show' page. If the user is on an admin page, display
        // the item on the admin show page, otherwise display it on the public show page.
        $referrer = $_SERVER['HTTP_REFERER'];
        $onAdminPage = strpos($referrer, '/admin');
        $url = "/items/show/$id";
        if ($onAdminPage)
        {
            $url = '/admin' . $url;
        }

        // Abandon the search request and redirect to the 'show' page.
        $this->getRedirector()->gotoUrl(WEB_ROOT . $url);
    }

    protected function setItemType($item)
    {
        if (!empty($item['item_type_id']))
            return;

        // Explicitly set the item_type_id for a newly added item. Normally in Omeka the admin
        // chooses the item type from a dropdown list, but AvantCustom hides that list.
        $item['item_type_id'] = Custom::getCustomItemTypeId();;
    }

    public function shortcode($args, $view)
    {
        // We are not currently using this method.
        $html = "";
        return $html;
    }

    protected function showItemHistory($item) {
        $db = get_db();
        $ownerId = $item->owner_id;

        // Get the name of the item's owner accounting for the possibility of that user's account having been deleted.
        $user = $db->getTable('User')->find($ownerId);
        $userName = $user ? $user->username : 'unknown';

        $dateAdded = $item->added;
        $dateModified = $item->modified;

        $html =  "<div class='item-owner panel'><h4>Item History</h4><p>Owner: $userName<br/>Added: " . self::getDate($dateAdded) . "<br/>Modified: " . self::getDate($dateModified) . "</p></div>";
        echo $html;

    }
}
