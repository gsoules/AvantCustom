<?php

try
{
    $item = get_current_record('item');
}
catch (Omeka_View_Exception $e)
{
    // This exception handler circumvents an Omeka anomaly where it incorrectly calls AvantCustom's
    // libraries\form.php file (this file) when attempting to display the form used to edit item types.
    // When the wrong form is called, an exception occurs. The best we can do it trap it to avoid an
    // error, but we don't know any way to get Omeka to call the correct form which is probably
    // admin\themes\default\item-types\form.php
    echo '<h4 style="color:red;">You must temporarily deactivate the AvantCustom plugin before editing an Item Type.</h4>';
    echo 'DO NOT click any buttons below.<br/><br/>';
    echo 'Deactivate the AvantCustom plugin now and then return to the Item Type editor.<br/>';
    echo 'When you are finished editing the Item Type, don\'t forget to reactivate AvantCustom.<br/><br/>';
    echo 'This inconvenience is caused by an Omeka bug that creates an incompatibility with the plugin.<br/><br/>';
    return;
}

echo js_tag('elements');
echo js_tag('tabs');
echo js_tag('items');
?>

<script type="text/javascript" charset="utf-8">
//<![CDATA[
// TinyMCE hates document.ready.
jQuery(window).load(function () {
    Omeka.Tabs.initialize();

    Omeka.Items.tagDelimiter = <?php echo js_escape(get_option('tag_delimiter')); ?>;
    Omeka.Items.enableTagRemoval();
    Omeka.Items.makeFileWindow();
    Omeka.Items.enableSorting();
    Omeka.Items.tagChoices('#tags', <?php echo js_escape(url(array('controller'=>'tags', 'action'=>'autocomplete'), 'default', array(), true)); ?>);

    Omeka.wysiwyg({
        mode: "none",
        forced_root_block: ""
    });

    // Must run the element form scripts AFTER reseting textarea ids.
    jQuery(document).trigger('omeka:elementformload');

    Omeka.Items.enableAddFiles(<?php echo js_escape(__('Add Another File')); ?>);
    Omeka.Items.changeItemType(<?php echo js_escape(url("items/change-type")) ?><?php if ($id = metadata('item', 'id')) echo ', '.$id; ?>);
});

jQuery(document).bind('omeka:elementformload', function (event) {
    Omeka.Elements.makeElementControls(event.target, <?php echo js_escape(url('elements/element-form')); ?>,'Item'<?php if ($id = metadata('item', 'id')) echo ', '.$id; ?>);
    Omeka.Elements.enableWysiwyg(event.target);
});
//]]>
</script>

<section class="seven columns alpha" id="edit-form">

    <?php echo flash(); ?>

    <?php
    echo item_image_gallery(array('linkWrapper' => array('class' => 'admin-thumb panel'), 'link' => array('target' => '_blank')), 'thumbnail', false); ?>
    
    <div id="item-metadata">
    <?php foreach ($tabs as $tabName => $tabContent): ?>
        <?php if (!empty($tabContent)): ?>
            <div id="<?php echo text_to_id(html_escape($tabName)); ?>-metadata">
            <fieldset class="set">
                <h2><?php echo html_escape(__($tabName)); ?></h2>
                <?php echo $tabContent; ?>        
            </fieldset>
            </div>     
        <?php endif; ?>
    <?php endforeach; ?>
    </div>

</section>
<?php echo $csrf; ?>
