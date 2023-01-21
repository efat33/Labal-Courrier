<?php
global $post;
$post_slug = $post->post_name;

$steps = [
    'labal-courrier-additional-information' => ['<i class="fa-solid fa-truck"></i>', __("Delivery", "labal-courrier")],
    'schedule-pickup' => ['<i class="fa-solid fa-truck-pickup"></i>', __("Pick up", "labal-courrier")],
    'customs-declaration' => ['<i class="fa-solid fa-file"></i>',  __("Invoice", "labal-courrier")],
    'labal-courrier-checkout' => ['<i class="fa-solid fa-clipboard-check"></i>',  __("Confirm", "labal-courrier")],
    'labal-courrier-payment' => ['<i class="fa-solid fa-cart-shopping"></i>',  __("Payment", "labal-courrier")],
    'finalize-shipment' => ['<i class="fa-solid fa-file-arrow-down"></i>',  __("Downloads", "labal-courrier")],
];

?>

<div class="lc-courier-steps-area">
    <div class="lc-courier-steps lc-grid rounded-3 mb-5 mt-5 py-4 bg-white">
        <?php foreach ($steps as $key => $value) { ?>
            <span class="text-center <?= $key == $post_slug ? 'current' : NULL ?>"><?= $value[0] . $value[1] ?></span>
        <?php } ?>
    </div>
</div>