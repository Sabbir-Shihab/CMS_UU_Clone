<footer class="footer-bg">
  @php
    $site = $site ?? [];
    $menus = $menus ?? ['footer_columns' => []];
    $socials = $site['socials'] ?? [];
    $copyright = $site['copyright'] ?? '© 2026 Uttara University | Dhaka, Bangladesh. All Rights Reserved.';
  @endphp
  <div class="container">
    <h3 class="footer-social-head">Connect With Us</h3>
    <ul class="footer-social">
      @foreach($socials as $social)
        <li>
          <a href="{{ $social['url'] ?? '#' }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] ?? '' }}">
            <i class="fab fa-{{ $social['icon'] ?? 'link' }}"></i>
          </a>
        </li>
      @endforeach
    </ul>
    <div class="footer-cols">
      @foreach(($menus['footer_columns'] ?? []) as $col)
        <div>
          <h3 class="footer-link-title">{{ $col['title'] ?? '' }}</h3>
          <ul class="footer-link">
            @foreach(($col['links'] ?? []) as $link)
              <li>
                <a href="{{ \App\Support\SiteContent::href($link['url'] ?? '#') }}"
                   @if(\App\Support\SiteContent::isExternal($link)) target="_blank" rel="noopener" @endif>{{ $link['label'] ?? '' }}</a>
              </li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>
    <p class="copyright">{{ $copyright }}</p>
  </div>
</footer>
<button class="to-top" type="button" aria-label="Back to top">↑</button>
