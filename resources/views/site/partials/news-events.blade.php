@php
    $short = function (string $title): string {
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
        if (mb_strlen($title) <= 52) {
            return $title;
        }

        return rtrim(mb_substr($title, 0, 52)).'...';
    };
@endphp
<div class="news-events-banner">
  <div class="container">
    <h3 class="dept-name-common">News &amp; Events</h3>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb news-breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
        <li class="breadcrumb-item active">News &amp; Events</li>
      </ol>
    </nav>
  </div>
</div>
<main class="page-wrap news-events-page">
  <div class="container">
    <ul class="news-events-list">
      @foreach ($items as $item)
        <li>
          <div class="news-img-box">
            <a href="{{ url('/'.$item['slug']) }}">
              <img src="{{ $item['image'] }}" alt="News&amp;Events" class="img-fluid">
            </a>
          </div>
          <div class="news-list-details-box">
            <a href="{{ url('/'.$item['slug']) }}">{{ $short($item['title']) }}</a>
            <h5 class="news-date news-list-date">{{ $item['date'] }}</h5>
            <a href="{{ url('/'.$item['slug']) }}" class="social-learn-more-btn">Read More</a>
          </div>
        </li>
      @endforeach
    </ul>

    @if ($pages > 1)
      <ul class="pagination news-pagination">
        <li class="{{ $page <= 1 ? 'disabled' : '' }}">
          @if ($page <= 1)
            <span class="page-link">Previous</span>
          @else
            <a class="page-link" href="{{ $page === 2 ? url('/news') : url('/news').'?page='.($page - 1) }}">Previous</a>
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
            <a class="page-link" href="{{ $num === 1 ? url('/news') : url('/news').'?page='.$num }}">{{ $num }}</a>
          </li>
        @endforeach
        <li class="{{ $page >= $pages ? 'disabled' : '' }}">
          @if ($page >= $pages)
            <span class="page-link">Next</span>
          @else
            <a class="page-link" href="{{ url('/news') }}?page={{ $page + 1 }}">Next</a>
          @endif
        </li>
      </ul>
    @endif
  </div>
</main>
