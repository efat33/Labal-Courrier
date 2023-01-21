<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
global $wpdb, $table_prefix;

$shipment_id = $_SESSION['shipment_id_customer_email'];

// get invoice row, in order to get invoice number 
$invoices = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_invoices WHERE shipment_id = '$shipment_id'");

$shipment = $wpdb->get_row("SELECT * FROM " . $table_prefix . "lc_shipments WHERE lc_shipment_ID = '$shipment_id'");

$pickup_date = $shipment->dispatch_date;
$selected_carrier = str_replace("CARRIER_", "", $shipment->selected_carrier_id);
$waybill_number = isset($shipment->tracking_number) ? $shipment->tracking_number : '';
$only_carrier_dir =  strtolower(str_replace('CARRIER_', '', $shipment->selected_carrier_id));

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="initial-scale=1.0">
  <meta name="format-detection" content="telephone=no">
  <title>MOSAICO Responsive Email Designer</title>

  <style type="text/css">
    body {
      margin: 0;
      padding: 0;
    }

    img {
      border: 0px;
      display: block;
    }

    .socialLinks {
      font-size: 6px;
    }

    .socialLinks a {
      display: inline-block;
    }

    .long-text p {
      margin: 1em 0px;
    }

    .long-text p:last-child {
      margin-bottom: 0px;
    }

    .long-text p:first-child {
      margin-top: 0px;
    }
  </style>
  <style type="text/css">
    /* yahoo, hotmail */
    .ExternalClass,
    .ExternalClass p,
    .ExternalClass span,
    .ExternalClass font,
    .ExternalClass td,
    .ExternalClass div {
      line-height: 100%;
    }

    .yshortcuts a {
      border-bottom: none !important;
    }

    .vb-outer {
      min-width: 0 !important;
    }

    .RMsgBdy,
    .ExternalClass {
      width: 100%;
      background-color: #3f3f3f;
      background-color: #3f3f3f
    }

    /* outlook/office365 add buttons outside not-linked images and safari have 2px margin */
    [o365] button {
      margin: 0 !important;
    }

    /* outlook */
    table {
      mso-table-rspace: 0pt;
      mso-table-lspace: 0pt;
    }

    #outlook a {
      padding: 0;
    }

    img {
      outline: none;
      text-decoration: none;
      border: none;
      -ms-interpolation-mode: bicubic;
    }

    a img {
      border: none;
    }

    @media screen and (max-width: 600px) {

      table.vb-container,
      table.vb-row {
        width: 95% !important;
      }

      .mobile-hide {
        display: none !important;
      }

      .mobile-textcenter {
        text-align: center !important;
      }

      .mobile-full {
        width: 100% !important;
        max-width: none !important;
      }
    }

    /* previously used also screen and (max-device-width: 600px) but Yahoo Mail doesn't support multiple queries */
  </style>
  <style type="text/css">
    #ko_doubleArticleBlock_1 .links-color a,
    #ko_doubleArticleBlock_1 .links-color a:link,
    #ko_doubleArticleBlock_1 .links-color a:visited,
    #ko_doubleArticleBlock_1 .links-color a:hover {
      color: #3f3f3f;
      color: #112A46;
      text-decoration: underline
    }

    #ko_textBlock_1 .links-color a,
    #ko_textBlock_1 .links-color a:link,
    #ko_textBlock_1 .links-color a:visited,
    #ko_textBlock_1 .links-color a:hover {
      color: #3f3f3f;
      color: #112A46;
      text-decoration: underline
    }

    .links-color a,
    .links-color a:link,
    .links-color a:visited,
    .links-color a:hover {
      color: #cccccc;
      color: #cccccc;
      text-decoration: underline
    }
  </style>

</head>
<!--[if !(gte mso 16)]-->

<body bgcolor="#3f3f3f" text="#919191" alink="#cccccc" vlink="#cccccc" style="margin: 0; padding: 0; background-color: #3f3f3f; color: #919191;">
  <!--<![endif]-->
  <center>



    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_logoBlock_1">
      <tbody>
        <tr>
          <td class="vb-outer" align="center" valign="top" style="padding-left: 9px; padding-right: 9px; font-size: 0;">
            <!--[if (gte mso 9)|(lte ie 8)]><table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0" width="570"><tr><td align="center" valign="top"><![endif]-->
            <!--
      -->
            <div style="margin: 0 auto; max-width: 570px; -mru-width: 0px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="9" style="border-collapse: separate; width: 100%; mso-cellspacing: 9px; border-spacing: 9px; max-width: 570px; -mru-width: 0px;" width="570" class="vb-row">

                <tbody>
                  <tr>
                    <td align="center" valign="top" style="font-size: 0;">
                      <div style="vertical-align: top; width: 100%; max-width: 368px; -mru-width: 0px;">
                        <!--
        -->
                        <table role="presentation" class="vb-content" border="0" cellspacing="9" cellpadding="0" width="368" style="border-collapse: separate; width: 100%; mso-cellspacing: 9px; border-spacing: 9px;">

                          <tbody>
                            <tr>
                              <td width="100%" valign="top" align="center" class="links-color">
                                <!--[if (lte ie 8)]><div style="display: inline-block; width: 350px; -mru-width: 0px;"><![endif]--><img border="0" hspace="0" align="center" vspace="0" width="350" style="border: 0px; display: block; vertical-align: top; height: auto; margin: 0 auto; color: #f3f3f3; font-size: 18px; font-family: Arial, Helvetica, sans-serif; width: 100%; max-width: 350px; height: auto;" src="https://mosaico.io/srv/f-sg7xty4/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fsg7xty4%2Famarillo_sobre_crema.png&amp;method=resize&amp;params=350%2Cnull">
                                <!--[if (lte ie 8)]></div><![endif]-->
                              </td>
                            </tr>

                          </tbody>
                        </table>
                      </div>
                    </td>
                  </tr>

                </tbody>
              </table>
            </div>
            <!--
    -->
            <!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->

          </td>
        </tr>
      </tbody>
    </table>
    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_textBlock_1">
      <tbody>
        <tr>
          <td class="vb-outer" align="center" valign="top" style="padding-left: 9px; padding-right: 9px; font-size: 0;">
            <!--[if (gte mso 9)|(lte ie 8)]><table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0" width="570"><tr><td align="center" valign="top"><![endif]-->
            <!--
      -->
            <div style="margin: 0 auto; max-width: 570px; -mru-width: 0px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="18" bgcolor="#faf5ea" width="570" class="vb-container" style="border-collapse: separate; width: 100%; background-color: #faf5ea; mso-cellspacing: 18px; border-spacing: 18px; max-width: 570px; -mru-width: 0px;">

                <tbody>
                  <tr>
                    <td class="long-text links-color" width="100%" valign="top" align="left" style="font-weight: normal; color: #112A46; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: left; line-height: normal;">
                      <p style="margin: 1em 0px; margin-top: 0px;">&nbsp;</p>
                      <p style="margin: 1em 0px;">Chère Madame, cher Monsieur,</p>
                      <p style="margin: 1em 0px;">Je vous prie de trouver en pièce-jointe vos documents d'expédition à
                        imprimer dans leur intégralité ainsi que votre facture</p>
                      <p style="margin: 1em 0px;">Disposez vos documents d'expédition dans une pochette plastique puis
                        collez-là sur votre colis. Rendez-vous dans le <strong>
                          <?php
                          if ($selected_carrier == "DHL") {
                            echo '<a target="_blank" href="https://locator.dhl.com/" style="color: #3f3f3f; color: #112A46; text-decoration: underline;">DHL SERVICE POINT</a>';
                          } else {
                            echo '<a href="https://www.ups.com/dropoff" style="color: #3f3f3f; color: #112A46; text-decoration: underline;">UPS ACCESS POINT</a>';
                          }
                          ?>
                          .&nbsp;</strong><br><br></p>
                      <p style="margin: 1em 0px;">Votre numéro de suivi est le&nbsp;: <strong><?= $waybill_number ?></strong><br><br>Vous pouvez suivre l'expédition de votre envoi sur <a href="http://www.moncourrierdefrance.com" style="color: #3f3f3f; color: #112A46; text-decoration: underline;">moncourrierdefrance.com</a>
                      </p>
                      <p style="margin: 1em 0px;">&nbsp;</p>
                      <p style="margin: 1em 0px;">Nous vous remercions pour votre confiance.</p>
                      <p style="margin: 1em 0px; margin-bottom: 0px;">&nbsp;</p>
                    </td>
                  </tr>

                </tbody>
              </table>
            </div>
            <!--
    -->
            <!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>
    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_doubleArticleBlock_1">
      <tbody>
        <tr>
          <td class="vb-outer" align="center" valign="top" style="padding-left: 9px; padding-right: 9px; font-size: 0;">
            <!--[if (gte mso 9)|(lte ie 8)]><table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0" width="570"><tr><td align="center" valign="top"><![endif]-->
            <!--
      -->
            <div style="margin: 0 auto; max-width: 570px; -mru-width: 0px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="9" bgcolor="#faf5ea" width="570" class="vb-row" style="border-collapse: separate; width: 100%; background-color: #faf5ea; mso-cellspacing: 9px; border-spacing: 9px; max-width: 570px; -mru-width: 0px;">

                <tbody>
                  <tr>
                    <td align="center" valign="top" style="font-size: 0;">
                      <div style="width: 100%; max-width: 552px; -mru-width: 0px;">
                        <!--[if (gte mso 9)|(lte ie 8)]><table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0" width="552"><tr><![endif]-->
                        <!--
        -->
                        <!--
            -->
                        <!--[if (gte mso 9)|(lte ie 8)]><td align="left" valign="top" width="276"><![endif]-->
                        <!--
      -->
                        <div class="mobile-full" style="display: inline-block; vertical-align: top; width: 100%; max-width: 276px; -mru-width: 0px; min-width: calc(50%); max-width: calc(100%); width: calc(304704px - 55200%);">
                          <!--
        -->
                          <table role="presentation" class="vb-content" border="0" cellspacing="9" cellpadding="0" style="border-collapse: separate; width: 100%; mso-cellspacing: 9px; border-spacing: 9px; -yandex-p: calc(2px - 3%);" width="276" align="left">

                            <tbody>
                              <tr>
                                <td width="100%" valign="top" align="center" class="links-color" style="padding-bottom: 9px;">
                                  <!--[if (lte ie 8)]><div style="display: inline-block; width: 258px; -mru-width: 0px;"><![endif]--><img border="0" hspace="0" align="center" vspace="0" width="258" style="border: 0px; display: block; vertical-align: top; height: auto; margin: 0 auto; color: #112A46; font-size: 13px; font-family: Arial, Helvetica, sans-serif; width: 100%; max-width: 258px; height: auto;" src="https://mosaico.io/srv/f-sg7xty4/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fsg7xty4%2F1.png&amp;method=resize&amp;params=258%2Cnull">
                                  <!--[if (lte ie 8)]></div><![endif]-->
                                </td>
                              </tr>
                              <tr>
                                <td width="100%" valign="top" align="left" style="font-weight: normal; color: #112A46; font-size: 18px; font-family: Arial, Helvetica, sans-serif; text-align: left;">
                                  <span style="font-weight: normal;">Mes documents d'expédition</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="long-text links-color" width="100%" valign="top" align="left" style="font-weight: normal; color: #112A46; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: left; line-height: normal;">
                                  <p style="margin: 1em 0px; margin-bottom: 0px; margin-top: 0px;">Envoi <strong><?= $selected_carrier ?>
                                      <?= $waybill_number ?></strong></p>
                                </td>
                              </tr>
                              <tr>
                                <td valign="top" align="left">
                                  <table role="presentation" cellpadding="6" border="0" align="left" cellspacing="0" style="border-spacing: 0; mso-padding-alt: 6px 6px 6px 6px; padding-top: 4px;">
                                    <tbody>
                                      <tr>
                                        <td width="auto" valign="middle" align="left">
                                          <span style="text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #64CB64; color: #112A46; font-size: 16px; font-family: Arial, Helvetica, sans-serif; border-radius: 20px; margin: 10px 0 0; display:block;">
                                            <?php
                                            if ($shipment->selected_carrier_id == 'CARRIER_UPS') {
                                            ?>
                                              <a style="text-decoration: none; font-weight: normal; color: #112A46; font-size: 16px; font-family: Arial, Helvetica, sans-serif;" target="_new" href="<?= LABAL_COURRIER_PLUGIN_URL . '/docs/' . $only_carrier_dir . '/' . $waybill_number . '/Documents-expedition-' . $waybill_number . '.pdf' ?>">J'imprime mes documents</a>
                                            <?php } else { ?>
                                              <a style="text-decoration: none; font-weight: normal; color: #112A46; font-size: 16px; font-family: Arial, Helvetica, sans-serif;" target="_new" href="<?= LABAL_COURRIER_PLUGIN_URL . '/docs/' . $only_carrier_dir . '/' . $waybill_number . '/Documents-expedition-' . $waybill_number . '.pdf' ?>">J'imprime mes documents</a>
                                            <?php } ?>
                                          </span>
                                          <?php
                                          if ($shipment->package_type == 'Package') {
                                          ?>
                                            <span style="text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #64CB64; color: #112A46; font-size: 16px; font-family: Arial, Helvetica, sans-serif; border-radius: 20px; margin: 10px 0 0; display:block;">
                                              <a style="text-decoration: none; font-weight: normal; color: #112A46; font-size: 16px; font-family: Arial, Helvetica, sans-serif;" target="_new" href="<?= LABAL_COURRIER_PLUGIN_URL . '/docs/' . $only_carrier_dir . '/' . $waybill_number . '/Facture-en-douane-' . $waybill_number . '.pdf' ?>">Facture en douane</a>
                                            </span>
                                          <?php } ?>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>

                            </tbody>
                          </table>
                          <!--
      -->
                        </div>
                        <!--[if (gte mso 9)|(lte ie 8)]></td><![endif]-->
                        <!--
          -->
                        <!--
            -->
                        <!--[if (gte mso 9)|(lte ie 8)]><td align="left" valign="top" width="276"><![endif]-->
                        <!--
      -->
                        <div class="mobile-full" style="display: inline-block; vertical-align: top; width: 100%; max-width: 276px; -mru-width: 0px; min-width: calc(50%); max-width: calc(100%); width: calc(304704px - 55200%);">
                          <!--
        -->
                          <table role="presentation" class="vb-content" border="0" cellspacing="9" cellpadding="0" style="border-collapse: separate; width: 100%; mso-cellspacing: 9px; border-spacing: 9px; -yandex-p: calc(2px - 3%);" width="276" align="left">

                            <tbody>
                              <tr>
                                <td width="100%" valign="top" align="center" class="links-color" style="padding-bottom: 9px;">
                                  <!--[if (lte ie 8)]><div style="display: inline-block; width: 258px; -mru-width: 0px;"><![endif]--><img border="0" hspace="0" align="center" vspace="0" width="258" style="border: 0px; display: block; vertical-align: top; height: auto; margin: 0 auto; color: #112A46; font-size: 13px; font-family: Arial, Helvetica, sans-serif; width: 100%; max-width: 258px; height: auto;" src="https://mosaico.io/srv/f-sg7xty4/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fsg7xty4%2F2.png&amp;method=resize&amp;params=258%2Cnull">
                                  <!--[if (lte ie 8)]></div><![endif]-->
                                </td>
                              </tr>
                              <tr>
                                <td width="100%" valign="top" align="left" style="font-weight: normal; color: #112A46; font-size: 18px; font-family: Arial, Helvetica, sans-serif; text-align: left;">
                                  <span style="font-weight: normal;">Ma facture Title</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="long-text links-color" width="100%" valign="top" align="left" style="font-weight: normal; color: #112A46; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: left; line-height: normal;">
                                  <p style="margin: 1em 0px; margin-bottom: 0px; margin-top: 0px;">Facture
                                    <strong><?= $shipment_id ?></strong>
                                  </p>
                                </td>
                              </tr>
                              <tr>
                                <td valign="top" align="left">
                                  <table role="presentation" cellpadding="6" border="0" align="left" cellspacing="0" style="border-spacing: 0; mso-padding-alt: 6px 6px 6px 6px; padding-top: 4px;">
                                    <tbody>
                                      <tr>
                                        <td width="auto" valign="middle" align="left" bgcolor="#64CB64" style="text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #64CB64; color: #112A46; font-size: 16px; font-family: Arial, Helvetica, sans-serif; border-radius: 20px;">
                                          <a style="text-decoration: none; font-weight: normal; color: #112A46; font-size: 16px; font-family: Arial, Helvetica, sans-serif;" target="_new" href="<?= LABAL_COURRIER_PLUGIN_URL . '/docs/' . $only_carrier_dir . '/' . $waybill_number . '/Facture-' . $invoices->inv_number  . '.pdf' ?>">Je télécharge ma facture</a>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>

                            </tbody>
                          </table>
                          <!--
      -->
                        </div>
                        <!--[if (gte mso 9)|(lte ie 8)]></td><![endif]-->
                        <!--
          -->
                        <!--
      -->
                        <!--[if (gte mso 9)|(lte ie 8)]></tr></table><![endif]-->
                      </div>
                    </td>
                  </tr>

                </tbody>
              </table>
            </div>
            <!--
    -->
            <!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>
    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_bigSocialBlock_1">
      <tbody>
        <tr>
          <td class="vb-outer" align="center" valign="top" style="padding-left: 9px; padding-right: 9px; font-size: 0;">
            <!--[if (gte mso 9)|(lte ie 8)]><table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0" width="570"><tr><td align="center" valign="top"><![endif]-->
            <!--
      -->
            <div style="margin: 0 auto; max-width: 570px; -mru-width: 0px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="18" bgcolor="#faf5ea" width="570" class="vb-container links-color socialLinks mobile-textcenter" style="font-size: 6px; border-collapse: separate; width: 100%; background-color: #faf5ea; mso-cellspacing: 18px; border-spacing: 18px; max-width: 570px; -mru-width: 0px;">

                <tbody>

                  <tr>
                    <td width="100%" valign="top" style="font-size: 6px; font-weight: normal; text-align: center;" align="center" class="links-color socialLinks mobile-textcenter">

                      &nbsp;<a style="display: inline-block; border-radius: 50px;" target="_new" href="https://www.facebook.com/moncourrierdefrance"><img border="0" src="https://mosaico.io/templates/versafix-1/img/icons/fb-rdcol-96.png" width="32" height="32" alt="Facebook" style="border: 0px; display: inline-block; vertical-align: top; padding-bottom: 0px;"></a>







                      &nbsp;<a style="display: inline-block; border-radius: 50px;" target="_new" href="https://wa.me/%2B33185851439"><img border="0" src="https://mosaico.io/templates/versafix-1/img/icons/wa-rdcol-96.png" width="32" height="32" alt="Whatsapp" style="border: 0px; display: inline-block; vertical-align: top; padding-bottom: 0px;"></a>



                      &nbsp;<a style="display: inline-block; border-radius: 50px;" target="_new" href="https://www.linkedin.com/company/mon-courrier-de-france"><img border="0" src="https://mosaico.io/templates/versafix-1/img/icons/in-rdcol-96.png" width="32" height="32" alt="Linkedin" style="border: 0px; display: inline-block; vertical-align: top; padding-bottom: 0px;"></a>







                      &nbsp;<a style="display: inline-block; border-radius: 50px;" target="_new" href="https://www.instagram.com/moncourrierdefrance/"><img border="0" src="https://mosaico.io/templates/versafix-1/img/icons/inst-rdcol-96.png" width="32" height="32" alt="Instagram" style="border: 0px; display: inline-block; vertical-align: top; padding-bottom: 0px;"></a>



                    </td>
                  </tr>


                </tbody>
              </table>
            </div>
            <!--
    -->
            <!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>


    <!-- footerBlock -->
    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_footerBlock_2">
      <tbody>
        <tr>
          <td class="vb-outer" align="center" valign="top" style="padding-left: 9px; padding-right: 9px; font-size: 0;">
            <!--[if (gte mso 9)|(lte ie 8)]><table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0" width="570"><tr><td align="center" valign="top"><![endif]-->
            <!--
      -->
            <div style="margin: 0 auto; max-width: 570px; -mru-width: 0px;">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; width: 100%; mso-cellspacing: 0px; border-spacing: 0px; max-width: 570px; -mru-width: 0px;" width="570" class="vb-row">

                <tbody>
                  <tr>
                    <td align="center" valign="top" style="font-size: 0; padding: 0 9px;">
                      <div style="vertical-align: top; width: 100%; max-width: 552px; -mru-width: 0px;">
                        <!--
        -->
                        <table role="presentation" class="vb-content" border="0" cellspacing="9" cellpadding="0" style="border-collapse: separate; width: 100%; mso-cellspacing: 9px; border-spacing: 9px;" width="552">

                          <tbody>
                            <tr>
                              <td class="long-text links-color" width="100%" valign="top" align="center" style="font-weight: normal; color: #919191; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: center;">
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </td>
                  </tr>

                </tbody>
              </table>
            </div>
            <!--
    -->
            <!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->
          </td>
        </tr>
      </tbody>
    </table>
    <!-- /footerBlock -->

  </center>
  <!--[if !(gte mso 16)]-->
</body>
<!--<![endif]-->

</html>