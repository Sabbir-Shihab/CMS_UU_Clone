@extends('admin.layout')
@section('title', 'Homepage banners')
@section('top', 'Homepage')
@section('content')
  @php $home = $home ?? ['slides'=>[], 'ticker'=>[]]; @endphp
  <div class="panel">
    <h1>Homepage banners &amp; ticker</h1>
    <p class="help">Upload the big rotating pictures on the homepage and edit the scrolling message line. No coding needed.</p>

    <form method="post" action="{{ route('admin.home.update') }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <h3 style="margin:0 0 8px;color:#0a1f3d;">Banner pictures</h3>
      @foreach(($home['slides'] ?? []) as $i => $slide)
        <div style="border:1px solid #d7e0ec;border-radius:12px;padding:14px;margin-bottom:12px;">
          <img class="cover-preview" src="{{ $slide['image'] }}" alt="">
          <input type="hidden" name="slide_image[{{ $i }}]" value="{{ $slide['image'] }}">
          <label>Alt text</label>
          <input type="text" name="slide_alt[{{ $i }}]" value="{{ $slide['alt'] ?? '' }}">
          <label>Optional link</label>
          <input type="text" name="slide_link[{{ $i }}]" value="{{ $slide['link'] ?? '' }}" placeholder="/admission or https://…">
          <label>Replace this picture</label>
          <input type="file" name="slide_file[{{ $i }}]" accept="image/*">
          <label style="font-weight:500;"><input type="checkbox" name="remove_slide[{{ $i }}]" value="1"> Remove this banner</label>
        </div>
      @endforeach

      <label>Add another banner picture</label>
      <input type="file" name="new_slide" accept="image/*">
      <label>Alt text for new picture</label>
      <input type="text" name="new_slide_alt" placeholder="Admission Fall 2026">

      <label>Scrolling ticker messages (one per line)</label>
      <textarea name="ticker" rows="6" style="width:100%;padding:12px;border:1px solid #c5d0e0;border-radius:10px;">{{ old('ticker', implode("\n", $home['ticker'] ?? [])) }}</textarea>
      <p class="field-note">You can include simple HTML links, e.g. &lt;a href="https://uttara.ac.bd"&gt;uttara.ac.bd&lt;/a&gt;</p>

      <div class="actions">
        <button class="btn lg" type="submit">Save homepage</button>
        <a class="btn ghost" href="/" target="_blank" rel="noopener">Preview homepage</a>
      </div>
    </form>
  </div>
@endsection
