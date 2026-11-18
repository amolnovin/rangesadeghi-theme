@if(!empty($fc['count']) || in_array($fc['slug'], $selectedCats, true) || !empty($fc['children']))
<div class="rs-filter-category">
  @if(!empty($fc['children']))
    <details {{ !empty($selectedCats) ? 'open' : '' }}>
      <summary><label class="filter-option"><input type="checkbox" name="cat" value="{{ $fc['slug'] }}" {{ in_array($fc['slug'], $selectedCats, true) ? 'checked' : '' }}><span>{{ $fc['name'] }}</span><b class="count">{{ rs_fa($fc['count']) }}</b></label><svg><use href="#i-chevron-down"/></svg></summary>
      <div class="rs-filter-children">@foreach($fc['children'] as $child) @include('partials.filter-category', ['fc' => $child, 'selectedCats' => $selectedCats]) @endforeach</div>
    </details>
  @else
    <label class="filter-option"><input type="checkbox" name="cat" value="{{ $fc['slug'] }}" {{ in_array($fc['slug'], $selectedCats, true) ? 'checked' : '' }}><span>{{ $fc['name'] }}</span><b class="count">{{ rs_fa($fc['count']) }}</b></label>
  @endif
</div>

@endif
