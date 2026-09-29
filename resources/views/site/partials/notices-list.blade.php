<div class="news-events-banner">
  <div class="container">
    <h3 class="dept-name-common">Notice &amp; Announcements</h3>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb news-breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">Notice &amp; Announcements</li>
      </ol>
    </nav>
  </div>
</div>
<main class="page-wrap notices-page">
  <div class="container">
    <ul class="notice-list">
      @foreach ($items as $item)
        <li>
          <div class="notice-date-box">
            <h3>{{ $item['day_label'] }} <span>{{ $item['month_label'] }}</span></h3>
          </div>
          <div class="notice-details-box">
            <a href="/{{ ltrim($item['slug'], '/') }}">{{ $item['title'] }}</a>
            <a href="/{{ ltrim($item['slug'], '/') }}" class="notice-learn-more-btn">Learn More</a>
          </div>
        </li>
      @endforeach
    </ul>

    @if ($pages > 1)
      <ul class="pagination news-pagination justify-content-center">
        <li class="{{ $page <= 1 ? 'disabled' : '' }}">
          @if ($page <= 1)
            <span class="page-link"><i class="fa fa-angle-double-left px-2"></i> Previous</span>
          @else
            <a class="page-link" href="{{ $page === 2 ? url('/notices') : url('/notices').'?page='.($page - 1) }}"><i class="fa fa-angle-double-left px-2"></i> Previous</a>
          @endif
        </li>
        @php
          $window = [];
          if ($pages <= 7) {
              $window = range(1, $pages);
          } else {
              $window = [1, 2, 3, 4, 5];
              if ($page > 5) {
                  $window = [1];
                  for ($i = max(2, $page - 1); $i <= min($pages - 1, $page + 1); $i++) {
                      $window[] = $i;
                  }
              }
              $window[] = $pages;
              $window = array_values(array_unique($window));
              sort($window);
          }
        @endphp
        @foreach ($window as $i => $num)
          @if ($i > 0 && $num > $window[$i - 1] + 1)
            <li class="disabled"><span class="page-link">…</span></li>
          @endif
          <li class="{{ $num === $page ? 'active' : '' }}">
            <a class="page-link" href="{{ $num === 1 ? url('/notices') : url('/notices').'?page='.$num }}">{{ $num }}</a>
          </li>
        @endforeach
        <li class="{{ $page >= $pages ? 'disabled' : '' }}">
          @if ($page >= $pages)
            <span class="page-link">Next <i class="fa fa-angle-double-right px-2"></i></span>
          @else
            <a class="page-link" href="{{ url('/notices') }}?page={{ $page + 1 }}">Next <i class="fa fa-angle-double-right px-2"></i></a>
          @endif
        </li>
      </ul>
    @endif
  </div>
</main>
