<div id="type-metadata-form">
<?php
// Set the item's item type ID. Normally in Omeka this form displays a dropdown list from
// which the user must choose an item type when adding a new item or editing an existing item.
$customItemTypeId = Custom::getCustomItemTypeId();
$item['item_type_id'] = $customItemTypeId;

echo element_set_form(get_current_record('item'), 'Item Type Metadata');
?>
</div>
<?php fire_plugin_hook('admin_items_form_item_types', array('item' => $item, 'view' => $this)); ?>
