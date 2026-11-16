@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      <div class="co-empty" style="margin:40px auto">
        <svg><use href="#i-search"/></svg>
        <h1>صفحه مورد نظر پیدا نشد (۴۰۴)</h1>
        <p>ممکن است آدرس تغییر کرده باشد یا صفحه حذف شده باشد. از لینک‌های زیر ادامه دهید:</p>
        <div>
          <a class="btn btn-primary" href="{{ home_url('/') }}"><svg><use href="#i-home"/></svg>صفحه اصلی</a>
          <a class="btn btn-outline" href="{{ rs_page_url('shop') }}"><svg><use href="#i-grid"/></svg>مشاهده فروشگاه</a>
        </div>
      </div>
    </div>
  </main>
@endsection
