@if(!empty($node['children']))
  <details class="mobile-category-node">
    <summary>{{ $node['name'] }} <svg><use href="#i-chevron-down"/></svg></summary>
    <div class="mobile-category-children">
      <a href="{{ $node['url'] }}">مشاهده همه {{ $node['name'] }}</a>
      @foreach($node['children'] as $child)
        @include('partials.mobile-category', ['node' => $child])
      @endforeach
    </div>
  </details>
@else
  <a class="mobile-category-leaf" href="{{ $node['url'] }}">{{ $node['name'] }}</a>
@endif
