@extends('layouts.app')

@section('content')
  <main class="page-main woocommerce" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="{{ rs_page_url('cart') }}">سبد خرید</a><svg><use href="#i-chevron-left"/></svg>
        <span class="current">تسویه حساب</span>
      </nav>
      {!! do_shortcode('[woocommerce_checkout]') !!}
    </div>
  </main>
@endsection
