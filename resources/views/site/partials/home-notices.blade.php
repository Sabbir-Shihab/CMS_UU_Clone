<section class="latest-news-section">
  <div class="container notice-grid">
    <div class="news-column">
      <ul class="article" id="notice">
        <li><a class="active" href="{{ url('/notices') }}">Notice &amp; Announcements</a></li>
      </ul>
      <div class="article-content">
        <ul class="latest-news-list">
          @foreach ($notices as $item)
            <li>
              <a class="news-list-title" href="/{{ ltrim($item['slug'], '/') }}">{{ $item['title'] }}</a>
              <p class="news-date news-list-date">{{ $item['date'] }}</p>
            </li>
          @endforeach
        </ul>
        <a class="social-learn-more-btn" href="{{ url('/notices') }}">See More</a>
      </div>
    </div>
    <div class="news-column">
      <ul class="article">
        <li><a class="active" href="{{ url('/news') }}">News &amp; Events</a></li>
      </ul>
      <div class="article-content">
        @include('site.partials.home-news-list', ['items' => $news])
        <a class="social-learn-more-btn" href="{{ url('/news') }}">See More</a>
      </div>
    </div>
  </div>
</section>
