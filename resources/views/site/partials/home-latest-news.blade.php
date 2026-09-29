@php
    $short = function (string $title): string {
        $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
        if (mb_strlen($title) <= 48) {
            return $title;
        }

        return rtrim(mb_substr($title, 0, 48)).'...';
    };
    $pages = array_chunk($items, 3);
@endphp
<section class="news-block">
  <div class="container">
    <h3 class="news-top-title">Latest News</h3>
    <div data-news>
      @foreach ($pages as $i => $pageItems)
        <div class="news-page{{ $i === 0 ? ' is-on' : '' }}">
          @foreach ($pageItems as $item)
            <article class="card">
              <a href="{{ url('/'.$item['slug']) }}">
                <img class="img-fluid card-img-top" src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
              </a>
              <div class="card-body">
                <a class="news-title" href="{{ url('/'.$item['slug']) }}">{{ $short($item['title']) }}</a>
                <p class="news-date">{{ $item['date'] }}</p>
              </div>
            </article>
          @endforeach
        </div>
      @endforeach
      <div class="news-home-actions">
        <div class="news-pager"></div>
        <a class="social-learn-more-btn" href="{{ url('/news') }}">View All</a>
      </div>
    </div>
  </div>
</section>
