<?php
$pageTitle = __('Custom Dashboard');
echo head(array('bodyclass'=>'index primary-secondary', 'title'=>$pageTitle)); ?>

<?php
$total_items = total_records('Item');
$total_tags = total_records('Tag');
$stats = array(
    array(link_to('items', null, $total_items), __(plural('item', 'items', $total_items))),
    array(link_to('tags', null, $total_tags), __(plural('tag', 'tags', $total_tags)))
); ?>
<?php $stats = apply_filters('admin_dashboard_stats', $stats, array('view' => $this)); ?>

<?php // Retrieve the latest version of Omeka by pinging the Omeka server. ?>
<?php $userRole = current_user()->role; ?>
<?php if ($userRole == 'super'): ?>
    <?php $latestVersion = latest_omeka_version(); ?>
    <?php if ($latestVersion and version_compare(OMEKA_VERSION, $latestVersion, '<')): ?>
        <div id="flash">
            <ul>
                <li class="success"><?php echo __('A new version of Omeka is available for download.'); ?>
                    <a href="http://omeka.org/download/"><?php echo __('Upgrade to %s', $latestVersion); ?></a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>

<section id="stats">
    <?php foreach ($stats as $statInfo): ?>
        <p><span class="number"><?php echo $statInfo[0]; ?></span><br><?php echo $statInfo[1]; ?></p>
    <?php endforeach; ?>
</section>

<?php $panels = array(); ?>

<?php ob_start(); ?>
<h2><?php echo __('Recent Modifications'); ?></h2>
<?php
$modifiedItems = get_db()->getTable('Item')->findBy(array('sort_field' => 'modified', 'sort_dir' => 'd'), 100);
set_loop_records('items', $modifiedItems);
foreach (loop('items') as $modifiedItems):
    ?>
    <div class="recent-row">
        <p class="recent"><?php echo link_to_item() . ' (Item ' . metadata($modifiedItems, array('Dublin Core', 'Identifier'), array('no_filter' => true)) . ')'; ?></p>
        <?php if (is_allowed($modifiedItems, 'edit')): ?>
            <p class="dash-edit"><?php echo link_to_item(__('Edit'), array(), 'edit'); ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php $panels[] = ob_get_clean(); ?>

<?php ob_start(); ?>
<h2><?php echo __('Recent Items'); ?></h2>
<?php
$db = get_db();
set_loop_records('items', get_recent_items(100));
foreach (loop('items') as $item):
    $user = $db->getTable('User')->find($item->owner_id);
    $userName = $user ? $user->username : 'unknown';
    $identifier = metadata($item, array('Dublin Core', 'Identifier'), array('no_filter' => true));
    ?>
    <div class="recent-row">
        <p class="recent"><?php echo link_to_item() . ' (' . $identifier . ') ' . $userName; ?></p>
        <?php if (is_allowed($item, 'edit')): ?>
            <p class="dash-edit"><?php echo link_to_item(__('Edit'), array(), 'edit'); ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php if (is_allowed('Items', 'add')): ?>
    <div class="add-new-link"><p><a class="add-new-item" href="<?php echo html_escape(url('items/add')); ?>"><?php echo __('Add a new item'); ?></a></p></div>
<?php endif; ?>
<?php $panels[] = ob_get_clean(); ?>

<?php $panels = apply_filters('admin_dashboard_panels', $panels, array('view' => $this)); ?>
<?php for ($i = 0; $i < count($panels); $i++): ?>
    <section class="five columns <?php echo ($i & 1) ? 'omega' : 'alpha'; ?>">
        <div class="panel">
            <?php echo $panels[$i]; ?>
        </div>
    </section>
<?php endfor; ?>

<?php fire_plugin_hook('admin_dashboard', array('view' => $this)); ?>

<?php echo foot(); ?>
