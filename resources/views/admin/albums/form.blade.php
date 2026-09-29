@extends('admin.layout')
@section('title', $album ? 'Edit album' : 'New album')
@section('top', $album ? 'Edit album' : 'New album')
@section('content')
  <div class="panel">
    <p class="help"><a href="{{ route('admin.albums.index') }}">← Albums</a></p>
    <h1>{{ $album ? 'Edit album' : 'Create photo album' }}</h1>
    <form method="post" action="{{ $album ? route('admin.albums.update', $album['id']) : route('admin.albums.store') }}" enctype="multipart/form-data">
      @csrf
      @if($album) @method('PUT') @endif
      <label>Album title</label>
      <input type="text" name="title" value="{{ old('title', $album['title'] ?? '') }}" required placeholder="9th Convocation">

      @if(!empty($album['photos']))
        <label>Current photos (uncheck to remove)</label>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">
          @foreach($album['photos'] as $photo)
            <label style="font-weight:500;border:1px solid #d7e0ec;border-radius:10px;padding:8px;">
              <img src="{{ $photo }}" alt="" style="width:100%;height:80px;object-fit:cover;border-radius:6px;display:block;margin-bottom:6px;">
              <input type="checkbox" name="keep_photo[]" value="{{ $photo }}" checked> Keep
            </label>
          @endforeach
        </div>
      @endif

      <label>Upload photos</label>
      <input type="file" name="photos[]" accept="image/*" multiple {{ $album ? '' : '' }}>
      <p class="field-note">You can select many photos at once.</p>

      <div class="actions">
        <button class="btn lg" type="submit">{{ $album ? 'Save album' : 'Create album' }}</button>
      </div>
    </form>
  </div>
@endsection
