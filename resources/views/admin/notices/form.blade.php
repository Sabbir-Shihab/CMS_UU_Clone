@extends('admin.layout')
@section('title', $post ? 'Edit notice' : 'Add notice')
@section('top', $post ? 'Edit notice' : 'Add notice')
@section('content')
  @php
    use App\Support\AdminPostHelper;
    $dateValue = old('published_on', AdminPostHelper::dateInputValue($post['published_at'] ?? null, $post['display_date'] ?? null));
  @endphp

  <div class="crumb">
    <a href="{{ route('admin.notices.index') }}"><i class="fa-solid fa-arrow-left"></i> All notices</a>
    <span class="sep">/</span>
    <span>{{ $post ? 'Edit' : 'New' }}</span>
  </div>

  <div class="editor-hero">
    <div>
      <p class="eyebrow">Notices</p>
      <h1>{{ $post ? 'Edit notice' : 'Add a notice' }}</h1>
      <p class="sub">Add a title, date, optional scanned photo, then save the details.</p>
    </div>
    @if($post)
      <a class="btn ghost xl" href="{{ url('/'.$post['slug']) }}" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> Preview</a>
    @endif
  </div>

  <form method="post" action="{{ $post ? route('admin.notices.update', $post['id']) : route('admin.notices.store') }}" enctype="multipart/form-data" class="split-editor">
    @csrf
    @if($post) @method('PUT') @endif

    <div class="form-stack">
      <section class="form-section">
        <div class="form-section-head"><div><p class="eyebrow">Step 1</p><h2>Title &amp; date</h2></div></div>
        <div class="form-section-body field-grid">
          <div class="field span-2">
            <label for="title">Notice title</label>
            <input id="title" type="text" name="title" value="{{ old('title', $post['title'] ?? '') }}" required placeholder="Example: Fall 2026 class schedule">
            @error('title')<p class="error">{{ $message }}</p>@enderror
          </div>
          <div class="field">
            <label for="published_on">Date</label>
            <input id="published_on" type="date" name="published_on" value="{{ $dateValue }}" required>
            @error('published_on')<p class="error">{{ $message }}</p>@enderror
          </div>
        </div>
      </section>

      <section class="form-section">
        <div class="form-section-head"><div><p class="eyebrow">Step 2</p><h2>Scan / photo (optional)</h2></div></div>
        <div class="form-section-body">
          <div class="upload-box">
            @if(!empty($post['image_path']))
              <img class="cover-preview" src="{{ AdminPostHelper::publicPath($post['image_path']) }}" alt="">
              <label style="font-weight:500;margin-top:10px;"><input type="checkbox" name="remove_cover" value="1"> Remove current photo</label>
            @endif
            <label for="cover" style="margin-top:8px;">Upload photo</label>
            <input id="cover" type="file" name="cover" accept="image/*">
            <p class="field-note">JPG/PNG, up to 5 MB</p>
            @error('cover')<p class="error">{{ $message }}</p>@enderror
          </div>
        </div>
      </section>

      <section class="form-section">
        <div class="form-section-head"><div><p class="eyebrow">Step 3</p><h2>Notice details</h2></div></div>
        <div class="form-section-body">
          <div class="photo-chooser">
            <button type="button" class="btn" data-choose-photo="post-body">
              <i class="fa-regular fa-image"></i> Choose photo
            </button>
            <div class="copy">
              <strong>Add a picture to the notice</strong>
              <span>Click Choose photo → pick from your computer → it appears in the text.</span>
              <span data-photo-status></span>
            </div>
          </div>
          <div class="editor-frame">
            <textarea id="post-body" name="body">{{ old('body', $post['content'] ?? '') }}</textarea>
          </div>
          @error('body')<p class="error">{{ $message }}</p>@enderror
        </div>
      </section>

      <div class="sticky-save">
        <div class="hint">Click save when finished.</div>
        <button class="btn xl" type="submit"><i class="fa-regular fa-floppy-disk"></i> {{ $post ? 'Save changes' : 'Publish notice' }}</button>
      </div>

      @if($post)
        <details class="danger-zone soft danger-fold">
          <summary><i class="fa-solid fa-triangle-exclamation"></i> Delete notice</summary>
          <div class="fold-body">
            <button class="btn red" type="submit" form="deleteNoticeForm" style="margin-top:10px;">Delete</button>
          </div>
        </details>
      @endif
    </div>

    <aside class="side-rail">
      <div class="rail-card">
        <h3>Checklist</h3>
        <ol>
          <li>Write the notice title</li>
          <li>Choose the correct date</li>
          <li>Upload a scan if needed</li>
          <li>Write the details</li>
          <li>Click Save / Publish</li>
        </ol>
      </div>
    </aside>
  </form>

  @if($post)
    <form id="deleteNoticeForm" method="post" action="{{ route('admin.notices.destroy', $post['id']) }}" onsubmit="return confirm('Delete this notice permanently?');" style="display:none;">
      @csrf
      @method('DELETE')
    </form>
  @endif
  @include('admin.partials.editor', ['selector' => '#post-body', 'height' => 480])
@endsection
