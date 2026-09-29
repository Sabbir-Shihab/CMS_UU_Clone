@extends('admin.layout')
@section('title', $post ? 'Edit news' : 'Add news')
@section('top', $post ? 'Edit news' : 'Add news')
@section('content')
  @php
    use App\Support\AdminPostHelper;
    $dateValue = old('published_on', AdminPostHelper::dateInputValue($post['published_at'] ?? null, $post['display_date'] ?? null));
  @endphp

  <div class="crumb">
    <a href="{{ route('admin.news.index') }}"><i class="fa-solid fa-arrow-left"></i> All news</a>
    <span class="sep">/</span>
    <span>{{ $post ? 'Edit' : 'New' }}</span>
  </div>

  <div class="editor-hero">
    <div>
      <p class="eyebrow">News</p>
      <h1>{{ $post ? 'Edit news story' : 'Add a news story' }}</h1>
      <p class="sub">Headline, story, then the photos that sit under the story — two side by side, then full width.</p>
    </div>
    @if($post)
      <a class="btn ghost xl" href="{{ url('/'.$post['slug']) }}" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> Preview</a>
    @endif
  </div>

  <form method="post" action="{{ $post ? route('admin.news.update', $post['id']) : route('admin.news.store') }}" enctype="multipart/form-data" class="split-editor">
    @csrf
    @if($post) @method('PUT') @endif

    <div class="form-stack">
      <section class="form-section">
        <div class="form-section-head"><div><p class="eyebrow">Step 1</p><h2>Headline &amp; date</h2></div></div>
        <div class="form-section-body field-grid">
          <div class="field span-2">
            <label for="title">Headline</label>
            <input id="title" type="text" name="title" value="{{ old('title', $post['title'] ?? '') }}" required placeholder="Example: UU hosts research seminar">
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
        <div class="form-section-head"><div><p class="eyebrow">Step 2</p><h2>Cover photo (optional)</h2></div></div>
        <div class="form-section-body">
          <div class="upload-box">
            @if(!empty($post['image_path']))
              <img class="cover-preview" src="{{ AdminPostHelper::publicPath($post['image_path']) }}" alt="">
              <label style="font-weight:500;margin-top:10px;"><input type="checkbox" name="remove_cover" value="1"> Remove current cover photo</label>
            @endif
            <label for="cover" style="margin-top:8px;">Choose a photo</label>
            <input id="cover" type="file" name="cover" accept="image/*">
            <p class="field-note">JPG/PNG, up to 5 MB</p>
            @error('cover')<p class="error">{{ $message }}</p>@enderror
          </div>
        </div>
      </section>

      <section class="form-section">
        <div class="form-section-head"><div><p class="eyebrow">Step 3</p><h2>Story text</h2><p>This text appears above the photo block.</p></div></div>
        <div class="form-section-body">
          <div class="editor-frame">
            <textarea id="post-body" class="news-editor" name="body">{{ old('body', $post['content'] ?? '') }}</textarea>
          </div>
          @error('body')<p class="error">{{ $message }}</p>@enderror
        </div>
      </section>

      @php
        $savedPhotos = \App\Support\NewsGallery::decode($post['gallery_json'] ?? null);
        $oldSlots = old('slots');
        $photoSlots = [];
        if (is_array($oldSlots)) {
            foreach ($oldSlots as $slot) {
                $photoSlots[] = is_array($slot) ? (string) ($slot['existing'] ?? '') : '';
            }
        } else {
            $photoSlots = $savedPhotos;
        }
        while (count($photoSlots) < 4) {
            $photoSlots[] = '';
        }
      @endphp
      <section class="form-section">
        <div class="form-section-head">
          <div>
            <p class="eyebrow">Step 4</p>
            <h2>Photos under the story</h2>
            <p>Always 4 places. Add more if the story needs them. Empty places are skipped.</p>
          </div>
        </div>
        <div class="form-section-body">
          <div class="callout" style="margin-top:12px;">
            <div class="layout-demo" aria-hidden="true">
              <div class="pair"><i></i><i></i></div>
              <i class="wide"></i>
            </div>
            <div>
              <strong>How they sit on the website</strong>
              <span>Photo 1 and 2 sit side by side. Photo 3 is full width underneath. Photo 4 is full width on the next row. Extra photos repeat: two side by side, then one full width.</span>
            </div>
          </div>
          <div class="photo-board" id="photoBoard">
            @foreach($photoSlots as $index => $path)
              <div class="photo-slot" data-photo-slot>
                <div class="photo-slot-top">
                  <span class="num">{{ $index + 1 }}</span>
                  <strong>Photo {{ $index + 1 }}</strong>
                  <span data-slot-place></span>
                </div>
                @if($path !== '')
                  <img class="slot-preview" src="{{ AdminPostHelper::publicPath($path) }}" alt="">
                @endif
                <input type="hidden" name="slots[{{ $index }}][existing]" value="{{ $path }}">
                <label>Select from your computer</label>
                <input type="file" name="slots[{{ $index }}][file]" accept="image/jpeg,image/png,image/webp,image/gif">
                <p class="field-note">JPG, PNG or WebP · max 5 MB{{ $path !== '' ? ' · choosing a new file replaces this one' : '' }}</p>
                @error('slots.'.$index.'.file')<p class="error">{{ $message }}</p>@enderror
                <div class="slot-actions">
                  <button type="button" class="btn ghost sm" data-slot-up>Up</button>
                  <button type="button" class="btn ghost sm" data-slot-down>Down</button>
                  <button type="button" class="btn red sm" data-slot-clear>Remove</button>
                </div>
              </div>
            @endforeach
          </div>
          <div class="photo-tools">
            <p class="field-note" style="margin:0;">Select each photo from your computer, then save.</p>
            <button type="button" class="btn" id="addPhotoSlot"><i class="fa-solid fa-plus"></i> Add another photo</button>
          </div>
        </div>
      </section>

      <section class="form-section">
        <div class="form-section-head">
          <div>
            <p class="eyebrow">Step 5 · optional</p>
            <h2>Text under the photos</h2>
            <p>Use this when a paragraph should sit below the pictures, before Share.</p>
          </div>
        </div>
        <div class="form-section-body">
          <div class="editor-frame">
            <textarea id="post-after" class="news-editor" name="content_after">{{ old('content_after', $post['content_after'] ?? '') }}</textarea>
          </div>
          @error('content_after')<p class="error">{{ $message }}</p>@enderror
        </div>
      </section>

      <div class="sticky-save">
        <div class="hint">Don’t forget to save when you finish.</div>
        <button class="btn xl" type="submit"><i class="fa-regular fa-floppy-disk"></i> {{ $post ? 'Save changes' : 'Publish news' }}</button>
      </div>

      @if($post)
        <details class="danger-zone soft danger-fold">
          <summary><i class="fa-solid fa-triangle-exclamation"></i> Delete news</summary>
          <div class="fold-body">
            <button class="btn red" type="submit" form="deleteNewsForm" style="margin-top:10px;">Delete</button>
          </div>
        </details>
      @endif
    </div>

    <aside class="side-rail">
      <div class="rail-card">
        <h3>Checklist</h3>
        <ol>
          <li>Write a clear headline</li>
          <li>Pick the correct date</li>
          <li>Add a cover photo if available</li>
          <li>Write the story</li>
          <li>Add the photos under the story (4, or more)</li>
          <li>Click Save / Publish</li>
        </ol>
      </div>
    </aside>
  </form>

  @if($post)
    <form id="deleteNewsForm" method="post" action="{{ route('admin.news.destroy', $post['id']) }}" onsubmit="return confirm('Delete this news story permanently?');" style="display:none;">
      @csrf
      @method('DELETE')
    </form>
  @endif
  @include('admin.partials.editor', ['selector' => 'textarea.news-editor', 'height' => 360])
  <template id="photoSlotTemplate">
    <div class="photo-slot" data-photo-slot>
      <div class="photo-slot-top">
        <span class="num"></span>
        <strong></strong>
        <span data-slot-place></span>
      </div>
      <input type="hidden" data-existing name="slots[0][existing]" value="">
      <label>Select from your computer</label>
      <input type="file" data-file name="slots[0][file]" accept="image/jpeg,image/png,image/webp,image/gif">
      <p class="field-note">JPG, PNG or WebP · max 5 MB</p>
      <div class="slot-actions">
        <button type="button" class="btn ghost sm" data-slot-up>Up</button>
        <button type="button" class="btn ghost sm" data-slot-down>Down</button>
        <button type="button" class="btn red sm" data-slot-clear>Remove</button>
      </div>
    </div>
  </template>
  <script>
    (function () {
      const board = document.getElementById('photoBoard');
      const template = document.getElementById('photoSlotTemplate');
      if (!board || !template) return;

      function placeLabels(total) {
        const labels = Array(total).fill('Full width');
        let index = 0;
        while (index < total) {
          const remaining = total - index;
          if (remaining === 1) {
            labels[index] = 'Full width';
            index += 1;
          } else if (remaining === 2) {
            labels[index] = 'Left, side by side';
            labels[index + 1] = 'Right, side by side';
            index += 2;
          } else {
            labels[index] = 'Left, side by side';
            labels[index + 1] = 'Right, side by side';
            labels[index + 2] = 'Full width, under the pair';
            index += 3;
          }
        }
        return labels;
      }

      function refresh() {
        const slots = [...board.querySelectorAll('[data-photo-slot]')];
        const labels = placeLabels(slots.length);
        slots.forEach((slot, index) => {
          slot.querySelector('.num').textContent = String(index + 1);
          slot.querySelector('strong').textContent = 'Photo ' + (index + 1);
          const place = slot.querySelector('[data-slot-place]');
          if (place) place.textContent = labels[index];
          const existing = slot.querySelector('[data-existing]') || slot.querySelector('input[type=hidden]');
          const file = slot.querySelector('[data-file]') || slot.querySelector('input[type=file]');
          if (existing) {
            existing.name = 'slots[' + index + '][existing]';
            existing.setAttribute('data-existing', '');
          }
          if (file) {
            file.name = 'slots[' + index + '][file]';
            file.id = 'slot-file-' + index;
            file.setAttribute('data-file', '');
          }
        });
      }

      board.addEventListener('click', (event) => {
        const slot = event.target.closest('[data-photo-slot]');
        if (!slot) return;
        if (event.target.closest('[data-slot-up]')) {
          const prev = slot.previousElementSibling;
          if (prev) board.insertBefore(slot, prev);
          refresh();
        }
        if (event.target.closest('[data-slot-down]')) {
          const next = slot.nextElementSibling;
          if (next) board.insertBefore(next, slot);
          refresh();
        }
        if (event.target.closest('[data-slot-clear]')) {
          const slots = board.querySelectorAll('[data-photo-slot]');
          if (slots.length > 4) {
            slot.remove();
          } else {
            const existing = slot.querySelector('input[type=hidden]');
            const file = slot.querySelector('input[type=file]');
            const preview = slot.querySelector('.slot-preview');
            if (existing) existing.value = '';
            if (file) file.value = '';
            if (preview) preview.remove();
          }
          refresh();
        }
      });

      document.getElementById('addPhotoSlot')?.addEventListener('click', () => {
        board.appendChild(template.content.cloneNode(true));
        refresh();
      });

      refresh();
    })();
  </script>
@endsection
