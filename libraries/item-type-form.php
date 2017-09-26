<div id="type-metadata-form">
<?php
// Emit the elements for the default item type. In this implementation, there is only one item type and
// thus the default type is the only type. The original Omeka implementation emits a dropdown list of item
// types here and the admin needs to select one which then causes that type's fields to be dynamically
// generated. That's an extra step for the admin so we eliminate the selector and force use of the default
// item type by setting the item's item_type_id to the id of the only type in the ItemType table.
$itemTypes = get_db()->getTable('ItemType')->findAll();
$defaultItemTypeId = $itemTypes[0]->id;
$item['item_type_id'] = $defaultItemTypeId;
echo element_set_form(get_current_record('item'), 'Item Type Metadata');
?>
</div>
<?php fire_plugin_hook('admin_items_form_item_types', array('item' => $item, 'view' => $this)); ?>
