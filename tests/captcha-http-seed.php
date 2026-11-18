<?php
/** Create short-lived test challenges with the real user-provided plugin. */
if (!class_exists('WP_MC_Captcha')) throw new RuntimeException('WP Math Captcha is not active');
$settings = WP_MC_Core::all();
$settings['min_time'] = 0;
$settings['block_bot_ua'] = 0;
update_option(WP_MC_Core::OPT, $settings);
$contact = WP_MC_Captcha::new_challenge();
$newsletter = WP_MC_Captcha::new_challenge();
$login = WP_MC_Captcha::new_challenge();
foreach ([$contact, $newsletter, $login] as $challenge) if (is_wp_error($challenge)) throw new RuntimeException($challenge->get_error_message());
$contactHtml = rs_view('pages.contact', ['dataPage' => 'contact']);
$homeHtml = rs_view('front-page', ['dataPage' => 'home']);
if (strpos($contactHtml, 'wpmc_token') === false || strpos($homeHtml, 'wpmc_token') === false) throw new RuntimeException('Captcha fields not rendered in theme forms');
echo 'RSCAPTCHA:' . wp_json_encode(['nonce' => wp_create_nonce('rs_public_nonce'), 'contact' => $contact, 'newsletter' => $newsletter, 'login' => $login]) . "\n";
