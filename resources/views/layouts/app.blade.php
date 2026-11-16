<!doctype html>
<html {!! get_language_attributes() !!} dir="rtl">
<head>
  <meta charset="{{ get_bloginfo('charset') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @wphead
  @stack('head')
</head>
<body {!! get_body_class() ? 'class="' . esc_attr(implode(' ', get_body_class())) . '"' : '' !!} {!! !empty($dataPageAttr) ? 'data-page="' . esc_attr($dataPageAttr) . '"' : '' !!}>
  @wpbodyopen

  @include('partials.svg-sprite')

  <a class="skip-link" href="#main-content">رفتن به محتوای اصلی</a>

  @include('partials.header')

  @yield('content')

  @include('partials.footer')

  @stack('scripts')
  @wpfooter
</body>
</html>
