<header>
  @php
    $site = $site ?? [];
    $menus = $menus ?? ['quick_links' => [], 'main_nav' => []];
    $logo = $site['logo_url'] ?? '/wp-content/themes/utheme/images/UULogo.webp';
    $mobileLogo = $site['mobile_logo_url'] ?? $logo;
    $phoneDisplay = $site['phone_display'] ?? 'Hunting Number: 096 40 UTTARA (888272)';
    $phoneTel = $site['phone_tel'] ?? '09640888272';
  @endphp
  <div class="header-top">
    <div class="container">
      <div class="header-top-row">
        <div class="logo-box">
          <a href="{{ url('/') }}" aria-label="Uttara University's homepage">
            <img src="{{ $logo }}" alt="Uttara University">
          </a>
        </div>
        <div class="header-tools">
          <div class="top-header">
            <div class="search" id="common-search">
              <form class="search-form" role="search" action="{{ url('/') }}" method="get">
                <input class="form-control input-search" type="text" name="s" id="s" placeholder="Search …" aria-label="Search">
                <input class="search_button" type="submit" value="Search" aria-label="Search">
              </form>
            </div>
            <ul class="header-top-list">
              <li><a class="phone" style="white-space: nowrap;" href="tel:{{ $phoneTel }}"><i class="fa fa-phone"></i> {{ $phoneDisplay }}</a></li>
            </ul>
          </div>
          <div class="header-menu">
            <ul class="header-top-list quick-links">
              @foreach(($menus['quick_links'] ?? []) as $item)
                @php
                  $children = $item['children'] ?? [];
                  $href = \App\Support\SiteContent::href($item['url'] ?? '#');
                  $ext = \App\Support\SiteContent::isExternal($item);
                  $liClass = trim(($item['li_class'] ?? '').(count($children) ? ' has-menu top-drop result-menu' : ''));
                  $aClass = $item['class'] ?? '';
                @endphp
                <li @class([$liClass => $liClass !== ''])>
                  @if(count($children))
                    <a class="dropdown-toggle {{ $aClass }}" href="#">{{ $item['label'] ?? '' }}</a>
                    <div class="dropdown-menu">
                      @foreach($children as $child)
                        <a href="{{ \App\Support\SiteContent::href($child['url'] ?? '#') }}"
                           @if(\App\Support\SiteContent::isExternal($child)) target="_blank" rel="noopener" @endif>{{ $child['label'] ?? '' }}</a>
                      @endforeach
                    </div>
                  @else
                    <a class="{{ $aClass }}" href="{{ $href }}" @if($ext) target="_blank" rel="noopener" @endif>
                      @if(($item['li_class'] ?? '') === 'erp')<i class="fa fa-sign-in"></i> @endif{{ $item['label'] ?? '' }}
                    </a>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="scroll-header">
    <div class="container">
      <nav class="navbar" aria-label="Primary">
        <a href="{{ url('/') }}">
          <img class="logo-mobile" src="{{ $mobileLogo }}" alt="Uttara University">
        </a>
        <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
          <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </button>
        <ul class="main-nav">
          @foreach(($menus['main_nav'] ?? []) as $item)
            @php
              $children = $item['children'] ?? [];
              $mega = !empty($item['mega']);
              $liClass = count($children) ? ($mega ? 'has-menu has-mega' : 'has-menu') : '';
              $href = \App\Support\SiteContent::href($item['url'] ?? '#');
            @endphp
            <li @class([$liClass => $liClass !== ''])>
              <a @if(!empty($item['nav'])) data-nav="{{ $item['nav'] }}" @endif href="{{ $href }}">{{ $item['label'] ?? '' }}</a>
              @if(count($children))
                <div class="dropdown-menu {{ $mega ? 'wide' : '' }}">
                  @if($mega)
                    <div class="mega-grid">
                      @foreach(array_chunk($children, (int) ceil(count($children) / 3) ?: 1) as $col)
                        <div class="mega-col">
                          @foreach($col as $child)
                            <a href="{{ \App\Support\SiteContent::href($child['url'] ?? '#') }}">{{ $child['label'] ?? '' }}</a>
                          @endforeach
                        </div>
                      @endforeach
                    </div>
                  @else
                    @foreach($children as $child)
                      <a href="{{ \App\Support\SiteContent::href($child['url'] ?? '#') }}">{{ $child['label'] ?? '' }}</a>
                    @endforeach
                  @endif
                </div>
              @endif
            </li>
          @endforeach
        </ul>
      </nav>
    </div>
  </div>
</header>
