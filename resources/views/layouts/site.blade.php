<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $page['title'] }}</title>
  <link rel="icon" href="/wp-content/themes/utheme/images/UULogo.webp">
  <link rel="stylesheet" href="{{ asset('vendor/fonts/site-fonts.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=77">
  @if (($page['slug'] ?? '') === 'faculty-bio')
  <link rel="stylesheet" href="/wp-content/plugins/3d-flipbook-dflip-lite/assets/css/dflip.min.css">
  @endif
</head>
<body data-cms="1" data-stack="1" data-page="{{ $page['menu_key'] ?? $page['slug'] }}">
  @include('partials.header')
  {!! $page['body'] !!}
  @include('partials.footer')
  <script src="{{ asset('js/main.js') }}?v=59"></script>
  @if (($page['slug'] ?? '') === 'faculty-bio')
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script>window.dFlipLocation = '/wp-content/plugins/3d-flipbook-dflip-lite/assets/';</script>
  <script src="/wp-content/plugins/3d-flipbook-dflip-lite/assets/js/dflip.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.DFLIP && window.DFLIP.parseBooks) { window.DFLIP.parseBooks(); }
      setTimeout(function () {
        var failed = document.querySelector('.df-error');
        var fallback = document.querySelector('.bio-book-fallback');
        var book = document.querySelector('._df_book');
        if (failed && fallback) {
          fallback.hidden = false;
          if (book) book.style.display = 'none';
        }
      }, 1800);
    });
  </script>
  @endif
</body>
</html>
