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

// The release of Omeka 2.6 broke code using TinyMCE. Now we have to choose which version to use.
$tinymce3 = version_compare(OMEKA_VERSION, '2.6', '<');

echo js_tag($tinymce3 ? 'vendor/tiny_mce/tiny_mce' : 'vendor/tinymce/tinymce.min');
echo js_tag('elements');
echo js_tag('tabs');
echo js_tag('items');

// Insert the Javascript for the Tiny MCE editor. The code comes from admin/themes/default/items/forms.php which is
// an Omeka core file. If that code changes in a future Omeka release, update tinymce4-script.php to match.
echo $this->partial($tinymce3 ? '/tinymce3-script.php' : '/tinymce4-script.php');
?>

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
