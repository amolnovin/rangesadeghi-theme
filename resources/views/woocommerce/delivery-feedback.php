<?php
/** @var \WC_Order|false $order @var bool $valid @var bool $success @var string $message @var array $items */
defined('ABSPATH') || exit;
?><!doctype html>
<html <?php language_attributes(); ?> dir="rtl"><head><meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>بازخورد تحویل سفارش</title><link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/resources/css/delivery-feedback.css'); ?>"></head><body>
<main class="feedback-shell"><header class="feedback-brand"><span>✓</span><b><?php echo esc_html(get_bloginfo('name')); ?></b></header><section class="feedback-card">
<?php if ($success || !$valid || !$this->eligible($order)) : ?>
  <div class="feedback-result" role="status"><span class="feedback-check" aria-hidden="true"><?php echo $success ? '✓' : '!'; ?></span><h1><?php echo $success ? 'بازخورد شما ثبت شد' : 'پیوند بازخورد'; ?></h1><p><?php echo esc_html($message); ?></p><a href="<?php echo esc_url(home_url('/')); ?>">بازگشت به فروشگاه</a></div>
<?php else : ?>
  <div class="feedback-kicker">پیگیری مرسوله</div><h1>سفارش به دستتان رسید؟</h1><p class="feedback-intro">برای سفارش <b dir="ltr">#<?php echo esc_html(rs_fa($order->get_order_number())); ?></b>، تجربه خود را با ما در میان بگذارید.</p>
  <?php if ($message !== '') : ?><p class="feedback-error" role="alert"><?php echo esc_html($message); ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" id="delivery-feedback-form">
    <?php wp_nonce_field('rs_feedback_' . $order->get_id() . '_' . $token); ?>
    <fieldset><legend>آیا سفارش خود را دریافت کرده‌اید؟</legend><div class="feedback-choice">
      <label><input required type="radio" name="received" value="yes"><span>بله، دریافت کرده‌ام</span></label>
      <label><input required type="radio" name="received" value="no"><span>خیر، دریافت نکرده‌ام</span></label>
    </div></fieldset>
    <div data-branch="no" hidden><fieldset><legend>وضعیت مرسوله چگونه است؟</legend><div class="feedback-reasons">
      <label><input type="radio" name="reason" value="carrier"> نزد شرکت ارسال‌کننده است و تحویل داده نشده است.</label>
      <label><input type="radio" name="reason" value="collection"> نزد شرکت ارسال‌کننده است و خودم مراجعه نکرده‌ام.</label>
      <label><input type="radio" name="reason" value="unknown"> از وضعیت ارسال سفارش اطلاعی ندارم.</label>
    </div></fieldset><fieldset><legend>آیا با شرکت ارسال‌کننده پیگیری کرده‌اید؟</legend><div class="feedback-choice"><label><input type="radio" name="contacted" value="yes"><span>بله</span></label><label><input type="radio" name="contacted" value="no"><span>خیر</span></label></div></fieldset><label class="feedback-label" for="no-detail">توضیح بیشتر <small>اختیاری</small></label><textarea id="no-detail" name="no_detail" maxlength="1000" rows="3" placeholder="اگر نکته‌ای برای پیگیری وجود دارد بنویسید"></textarea><p class="feedback-note">وضعیت سفارش تغییر نمی‌کند و چهار روز بعد دوباره برای شما پیام می‌فرستیم.</p></div>
    <div data-branch="yes" hidden><label class="feedback-label" for="delivery-date">روز و ساعت تحویل</label><input id="delivery-date" name="delivery_datetime" type="datetime-local" max="<?php echo esc_attr(wp_date('Y-m-d\TH:i')); ?>">
      <?php foreach (['pack_rating' => 'از بسته‌بندی راضی هستید؟', 'product_rating' => 'از محصول خریداری‌شده راضی هستید؟'] as $ratingName => $ratingTitle) : ?>
        <fieldset><legend><?php echo esc_html($ratingTitle); ?></legend><div class="feedback-stars" aria-label="<?php echo esc_attr($ratingTitle); ?>"><?php for ($n = 5; $n >= 1; $n--) : ?><input type="radio" id="<?php echo esc_attr($ratingName . $n); ?>" name="<?php echo esc_attr($ratingName); ?>" value="<?php echo esc_attr($n); ?>"><label for="<?php echo esc_attr($ratingName . $n); ?>" aria-label="<?php echo esc_attr($n); ?> ستاره">★</label><?php endfor; ?></div></fieldset>
      <?php endforeach; ?>
      <fieldset><legend>آیا این محصول را به دیگران توصیه می‌کنید؟</legend><div class="feedback-choice"><label><input type="radio" name="recommend" value="yes"><span>👍 بله</span></label><label><input type="radio" name="recommend" value="no"><span>👎 خیر</span></label></div></fieldset>
      <?php if ($items) : ?><label class="feedback-label" for="product">محصول مورد نظر</label><select id="product" name="product_id"><?php foreach ($items as $itemId => $itemName) : ?><option value="<?php echo esc_attr($itemId); ?>"><?php echo esc_html($itemName); ?></option><?php endforeach; ?></select><label class="feedback-label" for="review">نظر شما درباره محصول <small>اختیاری؛ پس از تأیید منتشر می‌شود</small></label><textarea id="review" name="review" rows="4" maxlength="2000" placeholder="نظر خود را درباره محصول بنویسید"></textarea><?php endif; ?>
      <label class="feedback-label" for="photos">تصاویر محصول <small>اختیاری؛ حداکثر ۳ تصویر و هر تصویر تا ۳ مگابایت</small></label><input id="photos" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple><p class="feedback-note">تصاویر در کتابخانه رسانه فروشگاه ذخیره می‌شوند؛ لطفاً اطلاعات شخصی در تصویر قرار ندهید.</p>
    </div>
    <button class="feedback-submit" type="submit">ثبت بازخورد <span aria-hidden="true">←</span></button>
  </form>
  <script>(function(){var form=document.getElementById('delivery-feedback-form');function update(){var value=(form.querySelector('[name=received]:checked')||{}).value;form.querySelectorAll('[data-branch]').forEach(function(el){el.hidden=el.dataset.branch!==value})}form.addEventListener('change',update);update()})();</script>
<?php endif; ?>
</section><p class="feedback-footer">از اینکه ما را برای بهتر شدن همراهی می‌کنید سپاسگزاریم.</p></main></body></html>
