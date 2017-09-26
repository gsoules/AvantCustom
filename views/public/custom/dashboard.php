<?php
$pageTitle = __('Dashboard');
echo head(array('bodyclass' => 'index primary-secondary', 'title' => $pageTitle));

$total_items = total_records('Item');
$stats = array(link_to('items', null, $total_items), __(plural('item', 'items', $total_items)));

$html = "<p>The Digital Archive contains $stats[0] $stats[1]</p>";

$user = current_user();
if ($user)
{
    $html .= "<p>You are logged in as: $user->username</p>";
    $html .= '<div><a href="' . WEB_ROOT . '/users/logout">Logout</a></div>';
}
else
{
    $html .= '<p><a href="' . WEB_ROOT . '/users/login">Administrator Login</a></p>';
}
echo $html;
echo foot();


