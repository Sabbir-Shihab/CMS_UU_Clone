<div class="convocation-banner"><div class="container">
  <h3 class="dept-name-common">Convocations</h3>
  <p class="dept-crumb"><a href="/">Home</a> / Gallery / Convocations</p>
</div></div>
<main class="page-wrap convocation-albums">
  <div class="container my-5">
    <div class="aigpl-breadcrumb-wrp">
      <a class="aigpl-breadcrumb" href="{{ url('/convocations') }}">Main Album</a> &raquo; {{ $album['title'] }}
    </div>
    <div class="aigpl-gallery aigpl-gallery-grid">
      @foreach ($album['photos'] as $photo)
        <div class="aigpl-cnt-wrp">
          <div class="aigpl-inr-wrp">
            <div class="aigpl-img-wrp">
              <a class="aigpl-img-link" href="{{ $photo }}" data-gallery="convocation">
                <img class="aigpl-img" src="{{ $photo }}" alt="{{ $album['title'] }}" loading="lazy">
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</main>
<div class="gallery-lightbox" hidden>
  <button type="button" class="gallery-lightbox-close" aria-label="Close">&times;</button>
  <img alt="">
</div>
