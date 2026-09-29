<div class="about-us-banner single-page-banner">
  <div class="container">
    <h3 class="dept-name-common">{{ $title }}</h3>
    <p class="article-date"><i class="fa-regular fa-calendar-days"></i> {{ $date }}</p>
  </div>
</div>
<main class="page-wrap news-article">
  <div class="container">
    <div class="description text-center">
      @if ($image)
        <img src="{{ $image }}" alt="{{ $title }}" class="img-fluid img-thumbnail">
      @endif
      <div class="text-dark text-left chancellor-para mb-3">{!! $content !!}</div>
      @if (! empty($galleryRows))
        <div class="news-gallery">
          @foreach ($galleryRows as $row)
            <div class="news-gallery-row{{ count($row) === 1 ? ' is-single' : '' }}">
              @foreach ($row as $photo)
                <figure>
                  <img src="{{ \App\Support\AdminPostHelper::publicPath($photo) }}" alt="{{ $title }}">
                </figure>
              @endforeach
            </div>
          @endforeach
        </div>
      @endif
      @if (! empty($contentAfter))
        <div class="text-dark text-left chancellor-para mb-3">{!! $contentAfter !!}</div>
      @endif
      <div class="news-share">
        <h3>Share</h3>
        <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.uttara.ac.bd/{{ $slug }}/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="https://twitter.com/intent/tweet?url=https://www.uttara.ac.bd/{{ $slug }}/" target="_blank" rel="noopener" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=https://www.uttara.ac.bd/{{ $slug }}/" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
      </div>
    </div>
  </div>
</main>
