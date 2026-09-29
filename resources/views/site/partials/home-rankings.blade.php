<section class="rank-block">
  <div class="container">
    <h3 class="news-top-title rank-title">Recognitions and Rankings at a Glance</h3>
    <div class="rank-slider" data-ranks data-show="4">
      <div class="rank-viewport">
        <div class="rank-track">
          @foreach ($logos as $logo)
            <div class="rank-slide">
              <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] }}">
            </div>
          @endforeach
        </div>
      </div>
      <div class="rank-pager"></div>
    </div>
  </div>
</section>
