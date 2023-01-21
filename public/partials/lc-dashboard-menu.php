<?php
global $post;
$post_slug = $post->post_name;
$menus = customer_dashboard_menus();

$current_menu_key = array_search($post_slug, array_column($menus, 1));
$current_menu =  $menus[$current_menu_key];
?>
<div class="lc-dashboard-mbl-menu dropdown d-md-none">
    <a class="nav-link dropdown-toggle shadow-sm px-3 py-2 rounded mb-3" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false"><?= $current_menu[0] ?></a>
    <ul class="dropdown-menu ul-dashboard-menu">
        <li class="my-2"><a href="<?= esc_url(home_url()) ?>"><i class="fa-solid fa-plus"></i> <?= esc_html_e("New Shipment", "labal-courrier") ?></a></li>
        <?php foreach ($menus as $key => $item) { ?>
            <li class="my-2 <?= $post_slug == $item[1] ? 'active' : '' ?>"><a href="<?= site_url($item[1]) ?>"><?= $item[2] . ' ' . $item[0] ?></a></li>
        <?php } ?>
    </ul>
</div>

<div class="lc-dashboard-desktop-menu d-none d-md-block">
    <a href="<?= esc_url(home_url()) ?>" class="a-new-shipment rounded p-3 d-inline-block"><i class="fa-solid fa-plus"></i> <?= esc_html_e("New Shipment", "labal-courrier") ?></a>

    <div class="dashboard-menu-wrapper mt-5 ms-2">
        <ul class="ul-dashboard-menu">
            <?php foreach ($menus as $key => $item) { ?>
                <li class="my-3 <?= $post_slug == $item[1] ? 'active' : '' ?>"><a href="<?= site_url($item[1]) ?>"><?= $item[2] . ' ' . $item[0] ?></a></li>
            <?php } ?>
        </ul>
    </div>
</div>