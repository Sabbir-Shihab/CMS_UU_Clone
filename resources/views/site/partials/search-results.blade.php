<main id="main" class="site-main">
  <div class="search-results-container">
    <header class="page-header">
      <div class="page-header-content">
        <h1 class="page-title">Search results for:</h1>
        <div class="page-description">{{ $query !== '' ? $query : '…' }}</div>
      </div>
    </header>

    @forelse ($results as $item)
      <div class="search-result-item">
        <article class="entry">
          <header class="entry-header">
            <h2 class="entry-title">
              <a href="{{ $item['url'] }}" rel="bookmark">{{ $item['title'] }}</a>
            </h2>
          </header>
        </article>
      </div>
    @empty
      @if ($query !== '')
        <div class="search-result-item">
          <article class="entry">
            <header class="entry-header">
              <h2 class="entry-title">No results found.</h2>
            </header>
          </article>
        </div>
      @endif
    @endforelse
  </div>
</main>
