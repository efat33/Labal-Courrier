<?php

if (!is_user_logged_in()) {
    echo '
        <script>
            window.location.replace("' . esc_url(site_url()) . '");
        </script>
    ';
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

$referral_code_discount = get_option('referral_code_discount', 10) == '' ? 10 : get_option('referral_code_discount', 10);
$referral_code_limit = get_option('referral_code_limit', 60) == '' ? 60 : get_option('referral_code_limit', 60);

?>

<script defer src="<?php echo plugin_dir_url(__FILE__); ?>../js/alpinejs.js"></script>

<div x-data="component()" x-init="init()" class="lc-customer-dashboar lc-invite-friend-page lc-form-container my-5">
    <div class="lc-dashboard-wrapper lc-grid grid-cols-1 md-grid-cols-4">
        <div class="lc-dashboard-left">
            <?php
            include LABAL_COURRIER_PLUGIN_PATH . 'public/partials/lc-dashboard-menu.php';
            ?>
        </div>
        <div class="lc-dashboard-right py-3">
            <h1 class="lr-form-heading mb-4"><?= __("Promo Code", "labal-courrier") ?></h1>

            <div class="lc-dashboard-right-wrapper px-2 py-4 p-md-5 mt-5 text-center">
                <div class="invite-friend-wrapper">
                    <h2 class="mb-2"><?= esc_html_e("Share your referral link with your friends!", "labal-courrier") ?></h2>
                    <p><?= sprintf(__("[Earn %s euros off your next shipment of packages over %s euros]", "labal-courrier"), $referral_code_discount, $referral_code_limit) ?></p>

                    <div class="invitation-link rounded px-2 py-3 my-5">
                        <?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>
                    </div>

                    <div class="invite-friend-share-area d-flex">
                        <a id="inv_share_facebook" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a id="inv_share_messenger" class="d-lg-none d-sm-flex" href="fb-messenger://share?link=<?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>" title="Messenger"><i class="fab fa-facebook-messenger"></i></a>
                        <a id="inv_share_twitter" target="_blank" href="https://twitter.com/intent/tweet?text=Invite%20Friend%20<?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a id="inv_share_whatsapp" class="d-none d-lg-flex" target="_blank" href="https://web.whatsapp.com://send?text=<?= urlencode('Recevez une remise de ' . $referral_code_discount . ' euros sur votre premier envoi de colis de plus de ' . $referral_code_limit . ' euros à l\'aide de ce lien') ?>%0A<?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>" title="Whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a id="inv_share_whatsapp" class="d-lg-none d-sm-flex" data-action="share/whatsapp/share" href="whatsapp://send?text=<?= urlencode('Recevez une remise de ' . $referral_code_discount . ' euros sur votre premier envoi de colis de plus de ' . $referral_code_limit . ' euros à l\'aide de ce lien') ?>%0A<?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>" title="Whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a id="copy_referral_link" data-toggle="tooltip" data-placement="top" data-copy="<?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>" title="<?= esc_attr_e('Copy to clipboard', 'labal-courrier') ?>" href="#"><i class="fa-solid fa-link"></i></a>
                        <a id="inv_share_email" target="_blank" href="mailto:?subject=Invite%20Friend&body=<?= esc_url(site_url()) . '/register/?r=' . $current_user->user_login ?>" title="Email"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    // const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    //     return new bootstrap.Tooltip(tooltipTriggerEl)
    // })

    function copyToClipboard(text, el) {
        var copyTest = document.queryCommandSupported('copy');
        var elOriginalText = el.attr('data-original-title');

        if (copyTest === true) {
            var copyTextArea = document.createElement("textarea");
            copyTextArea.value = text;
            document.body.appendChild(copyTextArea);
            copyTextArea.select();
            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'Copied!' : 'Whoops, not copied!';
                el.attr('data-original-title', msg).tooltip('show');
            } catch (err) {
                console.log('Oops, unable to copy');
            }
            document.body.removeChild(copyTextArea);
            el.attr('data-original-title', elOriginalText);
        } else {
            // Fallback if browser doesn't support .execCommand('copy')
            window.prompt("Copy to clipboard: Ctrl+C or Command+C, Enter", text);
        }
    }

    jQuery(function($) {
        $('[data-toggle="tooltip"]').tooltip();

        $('#copy_referral_link').click(function(e) {
            e.preventDefault()
            const text = $(this).attr('data-copy');
            const el = $(this);
            copyToClipboard(text, el);
        });
    });
</script>

<script>
    function component() {
        return {

        }
    }
</script>