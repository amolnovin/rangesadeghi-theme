@extends('layouts.app')

@section('content')
  <main class="page-main woocommerce" id="main-content">
    <div class="page-container">
      <nav class="breadcrumb" aria-label="مسیر صفحه">
        <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg>
        <a href="{{ rs_page_url('account') }}">حساب کاربری</a>
      </nav>
      <div class="co-card rs-account-endpoint">
        {!! do_shortcode('[woocommerce_my_account]') !!}
      </div>
    </div>
  </main>
@endsection
