<?php $view = get_view(); ?>

<div class="field">
    <div class="two columns alpha">
        <label for="custom_maintenance"><?php echo __('Maintenance'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('If checked, a "Down for maintenance" page will be displayed to public users. Logged in users will not be affected.'); ?></p>
        <?php echo $view->formCheckbox('custom_maintenance', true, array('checked' => (boolean)get_option('custom_maintenance'))); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="custom_tab_name"><?php echo __('Tab name'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Custom name for 'Item Type Metadata' tab on Edit Item page"); ?></p>
        <?php echo $view->formText('custom_tab_name', get_option('custom_tab_name')); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="custom_elements_display_order"><?php echo __('Display Order'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Provide a comma-separated list of element names in the order they should appear on the public Show page"); ?></p>
        <?php echo $view->formTextarea('custom_elements_display_order', get_option('custom_elements_display_order')); ?>
    </div>
</div>


