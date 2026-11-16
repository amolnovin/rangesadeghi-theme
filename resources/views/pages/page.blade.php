@extends('layouts.app')

@section('content')
  <main class="page-main" id="main-content">
    <div class="page-container">
      @php while (have_posts()) : the_post(); @endphp
        <nav class="breadcrumb" aria-label="مسیر صفحه">
          <a href="{{ home_url('/') }}">خانه</a><svg><use href="#i-chevron-left"/></svg><span class="current">{{ get_the_title() }}</span>
        </nav>

        <article class="article-card" style="padding:28px">
          <header class="page-head" style="margin-top:0">
            <h1>{{ get_the_title() }}</h1>
          </header>
          <div class="article-body">
            @php the_content(); @endphp
          </div>
        </article>
      @php endwhile; @endphp
    </div>
  </main>
@endsection
