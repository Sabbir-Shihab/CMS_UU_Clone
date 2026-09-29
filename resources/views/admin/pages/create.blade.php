@extends('admin.layout')
@section('title', 'New page')
@section('top', 'New page')
@section('content')
  @php $currentStatus = old('status', 'draft'); @endphp

  <div class="crumb">
    <a href="{{ route('admin.pages.index') }}"><i class="fa-solid fa-arrow-left"></i> All pages</a>
    <span class="sep">/</span>
    <span>New page</span>
  </div>

  <div class="editor-hero">
    <div>
      <p class="eyebrow">Create</p>
      <h1>Create a new page</h1>
      <p class="sub">Give it a name, write the content, then approve when it is ready for visitors.</p>
    </div>
  </div>

  <div class="how-steps">
    <div class="how-step"><span class="n">1</span><div><strong>Name it</strong><span>Choose a clear title</span></div></div>
    <div class="how-step"><span class="n">2</span><div><strong>Write</strong><span>Add the page content</span></div></div>
    <div class="how-step"><span class="n">3</span><div><strong>Publish</strong><span>Approve to go live</span></div></div>
  </div>

  <form method="post" action="{{ route('admin.pages.store') }}" class="split-editor">
    @csrf
    <div class="form-stack">
      <section class="form-section">
        <div class="form-section-head">
          <div>
            <p class="eyebrow">Step 1</p>
            <h2>Page name</h2>
          </div>
        </div>
        <div class="form-section-body field-grid">
          <div class="field span-2">
            <label for="title">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title') }}" required placeholder="Example: Campus Safety Guidelines">
            @error('title')<p class="error">{{ $message }}</p>@enderror
          </div>
          <div class="field span-2">
            <label for="slug">Web address (optional)</label>
            <input id="slug" type="text" name="slug" value="{{ old('slug') }}" placeholder="Leave blank to auto-create">
            @error('slug')<p class="error">{{ $message }}</p>@enderror
          </div>
        </div>
      </section>

      <section class="form-section">
        <div class="form-section-head">
          <div>
            <p class="eyebrow">Step 2</p>
            <h2>Content</h2>
          </div>
        </div>
        <div class="form-section-body">
          <div class="photo-chooser">
            <button type="button" class="btn" data-choose-photo="page-body">
              <i class="fa-regular fa-image"></i> Choose photo
            </button>
            <div class="copy">
              <strong>Add a picture</strong>
              <span>Click Choose photo → pick from your computer → it appears below.</span>
              <span data-photo-status></span>
            </div>
          </div>
          <div class="editor-frame">
            <textarea id="page-body" name="body">{{ old('body') }}</textarea>
          </div>
        </div>
      </section>

      <div class="sticky-save">
        <div class="hint">Tip: save as draft first, then approve after checking.</div>
        <div class="row-actions">
          <button class="btn xl" type="submit" name="status" value="draft">Save as draft</button>
          <button class="btn green xl" type="submit" name="status" value="published">Save &amp; show on website</button>
        </div>
      </div>
    </div>

    <aside class="side-rail">
      <div class="rail-card">
        <h3>Show on website?</h3>
        <div class="choice-grid">
          <label class="choice {{ $currentStatus === 'draft' ? 'is-on' : '' }}">
            <input type="radio" name="status" value="draft" @checked($currentStatus === 'draft')>
            <strong>Draft</strong>
            <span>Hidden for now</span>
          </label>
          <label class="choice live {{ $currentStatus === 'published' ? 'is-on' : '' }}">
            <input type="radio" name="status" value="published" @checked($currentStatus === 'published')>
            <strong>Approved</strong>
            <span>Live immediately</span>
          </label>
        </div>
      </div>
    </aside>
  </form>

  @include('admin.partials.editor', ['selector' => '#page-body', 'height' => 480])
  <script>
    document.querySelectorAll('.choice').forEach((label) => {
      label.addEventListener('click', () => {
        document.querySelectorAll('.choice').forEach((el) => el.classList.remove('is-on'));
        label.classList.add('is-on');
      });
    });
  </script>
@endsection
