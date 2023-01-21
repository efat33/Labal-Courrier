<?php
$current_user_id = get_current_user_id();
$referred_by_id = get_user_meta($current_user_id, 'mnfr_referred_by', true);

$sponsor_info = get_userdata($referred_by_id);
$sponsored_info = get_userdata($current_user_id);

$sponsor_name = $sponsor_info->first_name != '' ? $sponsor_info->first_name . ' ' . $sponsor_info->last_name : $sponsor_info->user_login;
$sponsored_name = $sponsored_info->first_name != '' ? $sponsored_info->first_name . ' ' . $sponsored_info->last_name : $sponsored_info->user_login;

$referral_code_discount = get_option('referral_code_discount', 10) == '' ? 10 : get_option('referral_code_discount', 10);
$referral_code_limit = get_option('referral_code_limit', 60) == '' ? 60 : get_option('referral_code_limit', 60);

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
    #ko_sideArticleBlock_6 .links-color a,
    #ko_sideArticleBlock_6 .links-color a:link,
    #ko_sideArticleBlock_6 .links-color a:visited,
    #ko_sideArticleBlock_6 .links-color a:hover {
      color: #3f3f3f;
      color: #3f3f3f;
      text-decoration: underline
    }

    #ko_footerBlock_2 .links-color a,
    #ko_footerBlock_2 .links-color a:link,
    #ko_footerBlock_2 .links-color a:visited,
    #ko_footerBlock_2 .links-color a:hover {
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



    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_logoBlock_4">
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
                                <!--[if (lte ie 8)]><div style="display: inline-block; width: 350px; -mru-width: 0px;"><![endif]--><img border="0" hspace="0" align="center" vspace="0" width="350" style="border: 0px; display: block; vertical-align: top; height: auto; margin: 0 auto; color: #f3f3f3; font-size: 18px; font-family: Arial, Helvetica, sans-serif; width: 100%; max-width: 350px; height: auto;" src="https://mosaico.io/srv/f-zjpstq9/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fzjpstq9%2Flogo.png&amp;method=resize&amp;params=350%2Cnull">
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
    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_sideArticleBlock_6">
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
                        <!--[if (gte mso 9)|(lte ie 8)]><td align="left" valign="top" width="138"><![endif]-->
                        <!--
      -->
                        <div class="mobile-full" style="display: inline-block; vertical-align: top; width: 100%; max-width: 138px; -mru-width: 0px; min-width: calc(25%); max-width: calc(100%); width: calc(304704px - 55200%);">
                          <!--
        -->
                          <table role="presentation" class="vb-content" border="0" cellspacing="9" cellpadding="0" width="138" align="left" style="border-collapse: separate; width: 100%; mso-cellspacing: 9px; border-spacing: 9px; -yandex-p: calc(2px - 3%);">

                            <tbody>
                              <tr>
                                <td width="100%" valign="top" align="center" class="links-color">
                                  <!--[if (lte ie 8)]><div style="display: inline-block; width: 120px; -mru-width: 0px;"><![endif]--><img border="0" hspace="0" align="center" vspace="0" width="120" style="border: 0px; display: block; vertical-align: top; height: auto; margin: 0 auto; color: #112A46; font-size: 13px; font-family: Arial, Helvetica, sans-serif; width: 100%; max-width: 120px; height: auto;" src="https://mosaico.io/srv/f-zjpstq9/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fzjpstq9%2FI%25CC%2581CONO%2520EMAIL%2520%25281%2529.png&amp;method=resize&amp;params=120%2Cnull">
                                  <!--[if (lte ie 8)]></div><![endif]-->
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
                        <!--[if (gte mso 9)|(lte ie 8)]><td align="left" valign="top" width="414"><![endif]-->
                        <!--
      -->
                        <div class="mobile-full" style="display: inline-block; vertical-align: top; width: 100%; max-width: 414px; -mru-width: 0px; min-width: calc(75%); max-width: calc(100%); width: calc(304704px - 55200%);">
                          <!--
        -->
                          <table role="presentation" class="vb-content" border="0" cellspacing="9" cellpadding="0" width="414" align="left" style="border-collapse: separate; width: 100%; mso-cellspacing: 9px; border-spacing: 9px; -yandex-p: calc(2px - 3%);">

                            <tbody>
                              <tr>
                                <td width="100%" valign="top" align="left" style="font-weight: normal; color: #112A46; font-size: 18px; font-family: Arial, Helvetica, sans-serif; text-align: left;">
                                  <span style="font-weight: normal;">Félicitations, vous avez reçu <?= $referral_code_discount ?> EUR à dépenser
                                    avec Mon courrier de France</span>
                                </td>
                              </tr>
                              <tr>
                                <td class="long-text links-color" width="100%" valign="top" align="left" style="font-weight: normal; color: #112A46; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: left; line-height: normal;">
                                  <p style="margin: 1em 0px; margin-top: 0px;">Chère/Cher <?= $sponsor_name ?><br><br><?= $sponsored_name ?>
                                    a effectué un envoi de colis éligible après avoir utilisé votre code
                                    d'invitation. Vous avez reçu un bon de réduction de <?= $referral_code_discount ?> EUR à dépenser pour un
                                    prochain envoi. Vous pouvez trouver votre bon de réduction dans la section des codes
                                    promotionnels de votre espace personnel.</p>
                                  <p style="margin: 1em 0px;">Merci d'avoir choisi d'envoyer vos colis sur notre
                                    plateforme.</p>
                                  <p style="margin: 1em 0px;">L'Equipe Mon courrier de France</p>
                                  <p style="margin: 1em 0px; margin-bottom: 0px;">&nbsp;</p>
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
    <table role="presentation" class="vb-outer" width="100%" cellpadding="0" border="0" cellspacing="0" bgcolor="#faf5ea" style="background-color: #faf5ea;" id="ko_bigSocialBlock_7">
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
                            <tr>
                              <td width="100%" valign="top" align="center" style="font-weight: normal; color: #ffffff; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: center;">
                                <a href="[unsubscribe_link]" style="color: #000000; text-decoration: underline;" target="_new"></a>
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