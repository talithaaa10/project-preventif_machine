@php
  use Illuminate\Support\Facades\Route;
@endphp

<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)
      @php
        $activeClass = null;
        if (isset($submenu->slug) && Route::currentRouteName() === $submenu->slug) {
            $activeClass = 'active';
        } elseif (isset($submenu->url) && request()->is(trim($submenu->url, '/') . '*')) {
            $activeClass = 'active';
        }
      @endphp

      <li class="menu-item {{ $activeClass }}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0);' }}" class="menu-link">
          @if (isset($submenu->icon))
            <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
        </a>
      </li>
    @endforeach
  @endif
</ul>
