<div class="convocation-banner"><div class="container">
  <h3 class="dept-name-common">Video Gallery</h3>
  <p class="dept-crumb"><a href="/">Home</a> / Gallery / Video Gallery</p>
</div></div>
<main class="page-wrap video-gallery-page">
  <div class="container my-5">
    <div class="video-gallery-grid">
      @forelse($videos as $video)
        <article class="video-card">
          <a href="https://www.youtube.com/watch?v={{ $video['id'] }}" target="_blank" rel="noopener">
            <img src="/uploads/youtube/{{ $video['id'] }}.jpg" alt="">
            <span class="video-play"><i class="fa-solid fa-play"></i></span>
          </a>
          <h3><a href="https://www.youtube.com/watch?v={{ $video['id'] }}" target="_blank" rel="noopener">{{ $video['title'] ?? 'Video' }}</a></h3>
        </article>
      @empty
        <p>No videos yet.</p>
      @endforelse
    </div>
  </div>
</main>
