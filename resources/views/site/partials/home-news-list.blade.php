<ul class="latest-news-list">
  @foreach ($items as $item)
    <li>
      <a class="news-list-title" href="{{ url('/'.$item['slug']) }}">{{ $item['title'] }}</a>
      <p class="news-date news-list-date">{{ $item['date'] ?? $item['display_date'] ?? '' }}</p>
    </li>
  @endforeach
</ul>
