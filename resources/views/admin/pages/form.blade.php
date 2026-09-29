@extends('admin.layout')
@section('title', $page['title'])
@section('top', 'Editing page')
@section('content')
  @php
    $isPublished = ($page['status'] ?? 'published') === 'published';
    $isHome = in_array($page['slug'] ?? '', ['home', 'index'], true);
    $shortTitle = $page['short_title'] ?? trim(explode('|', $page['title'])[0]);
    $currentStatus = old('status', $page['status'] ?? 'published');
    $sections = $form['sections'] ?? [];
    $sectionMode = $form['section_mode'] ?? 'single';
    $publicUrl = $isHome ? url('/') : url('/'.$page['slug']);
    $publicPath = $isHome ? '/' : '/'.$page['slug'];
    $partName = $sectionMode === 'tabs' ? 'tab' : 'section';
    $firstLabel = $sections[0]['label'] ?? 'Main content';
    $formatWhen = function (?string $value): string {
        if (! $value) {
            return 'Not saved yet';
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->timezone(config('app.timezone'))->format('d M Y, h:i A');
        } catch (\Throwable) {
            return $value;
        }
    };
  @endphp

  <div class="content-inner has-dock">
  <div class="crumb">
    <a href="{{ route('admin.pages.index') }}"><i class="fa-solid fa-arrow-left"></i> All pages</a>
    <span class="sep">/</span>
    <span>{{ $shortTitle }}</span>
  </div>

  <div class="editor-hero">
    <div>
      <p class="eyebrow">Website page editor</p>
      <h1>{{ $shortTitle }}</h1>
      <p class="sub">Edit one {{ $partName }} at a time. What you save here replaces that same {{ $partName }} on the live page.</p>
      <div class="meta-chips">
        @if($isPublished)
          <span class="badge badge-live"><i class="fa-solid fa-check"></i> Live on website</span>
        @else
          <span class="badge badge-draft"><i class="fa-regular fa-clock"></i> Draft — visitors cannot see</span>
        @endif
        <span class="meta-chip"><i class="fa-solid fa-link"></i> {{ $publicPath }}</span>
        <span class="meta-chip">Last saved {{ $formatWhen($page['updated_at'] ?? null) }}</span>
      </div>
    </div>
    <div class="row-actions">
      <a class="btn ghost" href="{{ $publicUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> Open live page</a>
    </div>
  </div>

  <div class="where-box">
    <div class="where-card">
      <h3>Where your changes appear</h3>
      <div class="site-path" id="sitePath">
        <span class="seg">Website</span>
        <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
        <span class="seg">{{ $shortTitle }}</span>
        <span class="arrow"><i class="fa-solid fa-chevron-right"></i></span>
        <span class="seg is-active" data-active-seg>{{ $firstLabel }}</span>
      </div>
      <div class="where-url">
        <span>Live address:</span>
        <a href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicPath }}</a>
        @if($sectionMode === 'tabs')
          <span>· then open the matching tab</span>
        @endif
      </div>
    </div>
    <div class="guide-card">
      <h3>3 easy steps</h3>
      <ol class="guide-list">
        <li><span class="n">1</span><span>Pick a <strong>fixed {{ $partName }}</strong> on the left</span></li>
        <li><span class="n">2</span><span>Update <strong>text / photo</strong> under that {{ $partName }}</span></li>
        <li><span class="n">3</span><span>Click <strong>Save changes</strong> at the bottom</span></li>
      </ol>
    </div>
  </div>

  <div class="now-editing" id="nowEditing">
    <div>
      <p class="label">You are editing now</p>
      <p class="value" data-now-label>{{ $firstLabel }}</p>
      <p class="hint" data-now-hint>
        @if($sectionMode === 'tabs')
          Visitors see this as a tab on {{ $shortTitle }}.
        @else
          Visitors see this as a section on {{ $shortTitle }}.
        @endif
      </p>
    </div>
    <span class="badge badge-live"><i class="fa-solid fa-lock"></i> {{ ucfirst($partName) }} name is fixed</span>
  </div>

  <form method="post" action="{{ route('admin.pages.update', $page['slug']) }}" id="pageEditForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="layout" value="{{ $form['layout'] }}">
    <input type="hidden" name="banner_class" value="{{ $form['banner_class'] }}">
    <input type="hidden" name="crumb" value="{{ $form['crumb'] }}">
    <input type="hidden" name="section_mode" value="{{ $sectionMode }}">

    <div class="form-stack">
      <details class="form-section" style="padding:0;">
        <summary class="form-section-head" style="cursor:pointer;list-style:none;">
          <div>
            <p class="eyebrow">Optional</p>
            <h2>Page title &amp; banner</h2>
            <p>Only change this if the top heading on the website needs updating.</p>
          </div>
          <span class="meta-chip">Usually leave as-is</span>
        </summary>
        <div class="form-section-body field-grid">
          <div class="field span-2">
            <label for="page-title">Browser tab title <span class="muted">(top of browser window)</span></label>
            <input id="page-title" type="text" name="title" value="{{ old('title', $page['title']) }}" required>
            @error('title')<p class="error">{{ $message }}</p>@enderror
          </div>
          @if($form['layout'] !== 'raw')
            <div class="field {{ $form['layout'] === 'admission' ? '' : 'span-2' }}">
              <label for="banner_title">Big heading on the page <span class="muted">(top banner)</span></label>
              <input id="banner_title" type="text" name="banner_title" value="{{ old('banner_title', $form['banner_title']) }}">
            </div>
          @endif
          @if($form['layout'] === 'admission')
            <div class="field">
              <label for="banner_sub">Banner subtitle</label>
              <input id="banner_sub" type="text" name="banner_sub" value="{{ old('banner_sub', $form['banner_sub']) }}">
            </div>
          @endif
        </div>
      </details>

      <section class="form-section" style="padding:0;overflow:visible;">
        <div class="form-section-head" style="border-radius: var(--radius) var(--radius) 0 0;">
          <div>
            <p class="eyebrow">Main work area</p>
            <h2>
              @if($sectionMode === 'tabs')
                Choose a website tab, then update its content
              @else
                Choose a page section, then update its content
              @endif
            </h2>
            <p>Left side = what visitors click. Right side = the text/photos under it.</p>
          </div>
          <span class="meta-chip">{{ count($sections) }} {{ $partName }}(s)</span>
        </div>

        <div class="doc-workspace" style="padding:16px;">
          <aside class="doc-nav" id="docNav">
            <div class="doc-nav-head">
              <strong>{{ $sectionMode === 'tabs' ? 'Website tabs' : 'Page sections' }}</strong>
              <span>Click one — names stay the same on the site</span>
            </div>
            <div class="doc-nav-list">
              @foreach($sections as $i => $section)
                <button type="button"
                        class="doc-nav-item {{ $i === 0 ? 'is-on' : '' }}"
                        data-doc-target="doc-{{ $section['key'] }}"
                        data-doc-label="{{ $section['label'] }}"
                        data-doc-hint="@if($sectionMode === 'tabs')Visitors open the “{{ $section['label'] }}” tab on {{ $shortTitle }}.@else This block appears under “{{ $section['label'] }}” on {{ $shortTitle }}.@endif">
                  <span class="num">{{ $i + 1 }}</span>
                  <span>
                    <b>{{ $section['label'] }}</b>
                    <small>Edit content under this</small>
                  </span>
                </button>
              @endforeach
            </div>
          </aside>

          <div class="doc-panels">
            @foreach($sections as $i => $section)
              @php
                $key = $section['key'];
                $html = old('sections.'.$key, $section['html'] ?? '');
              @endphp
              <div class="doc-panel {{ $i === 0 ? 'is-on' : '' }}" id="doc-{{ $key }}" data-doc-panel data-label="{{ $section['label'] }}">
                <div class="doc-panel-head">
                  <div>
                    <p class="eyebrow">Updating {{ $partName }} {{ $i + 1 }} of {{ count($sections) }}</p>
                    <h2>{{ $section['label'] }}</h2>
                    <p>
                      @if($sectionMode === 'tabs')
                        Changes here show when someone clicks the <strong>{{ $section['label'] }}</strong> tab on the website.
                      @else
                        Changes here show in the <strong>{{ $section['label'] }}</strong> part of the page.
                      @endif
                    </p>
                  </div>
                  <span class="badge badge-live"><i class="fa-solid fa-lock"></i> Name fixed</span>
                </div>
                <div class="doc-panel-body">
                  <div class="callout" style="margin-bottom:14px;">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                      <strong>On the website:</strong>
                      <span>
                        {{ $shortTitle }}
                        →
                        {{ $section['label'] }}
                        ·
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener">{{ $publicPath }}</a>
                      </span>
                    </div>
                  </div>

                  <div class="file-select" data-photo-box="section-body-{{ $key }}">
                    <div class="meta">
                      <strong>Add a photo to this {{ $partName }}</strong>
                      <span>Select from your computer · JPG / PNG / WebP · max 10 MB</span>
                      <span data-photo-status></span>
                    </div>
                    <div>
                      <label class="file-btn" for="photo-input-{{ $key }}">
                        <i class="fa-regular fa-folder-open"></i> Select photo
                      </label>
                      <input id="photo-input-{{ $key }}" type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-choose-photo-input="section-body-{{ $key }}">
                    </div>
                    <div class="preview" data-photo-preview>
                      <img alt="Selected preview" src="">
                      <span class="muted">Photo will be inserted into the text below.</span>
                    </div>
                  </div>

                  <label for="section-body-{{ $key }}">Text &amp; photos under “{{ $section['label'] }}”</label>
                  <div class="editor-frame">
                    <textarea id="section-body-{{ $key }}" class="page-section-body" name="sections[{{ $key }}]" rows="12">{{ $html }}</textarea>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </section>

      <div class="split-editor">
        <div class="form-stack">
          <div class="sticky-save save-dock">
            <div>
              <div class="hint" style="margin-bottom:4px;">
                Saving updates
                <span class="editing-chip" data-save-chip>{{ $shortTitle }} → {{ $firstLabel }}</span>
                and every other {{ $partName }} on this page.
              </div>
              <div class="hint">Tip: open the live page in another tab to check after saving.</div>
            </div>
            <div class="row-actions">
              <button class="btn xl" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save changes</button>
              @if(! $isHome && ! $isPublished)
                <button class="btn green xl" type="submit" name="status" value="published">Save &amp; make live</button>
              @endif
            </div>
          </div>

          <details class="danger-zone soft danger-fold">
            <summary><i class="fa-solid fa-triangle-exclamation"></i> Danger zone — clear or delete</summary>
            <div class="fold-body">
              <p class="muted" style="margin:8px 0 0;">These actions cannot be undone easily.</p>
              <div class="row-actions" style="margin-top:10px;">
                <button class="btn red" type="submit" form="clearContentForm">Clear all text</button>
                @if(! $isHome)
                  <button class="btn red" type="submit" form="deletePageForm">Delete whole page</button>
                @endif
              </div>
            </div>
          </details>
        </div>

        <aside class="side-rail">
          <div class="rail-card">
            <h3>Show this page on the website?</h3>
            @if(! $isHome)
              <p class="hint">Draft = only admins. Approved = everyone can open {{ $publicPath }}.</p>
              <div class="choice-grid">
                <label class="choice {{ $currentStatus === 'draft' ? 'is-on' : '' }}">
                  <input type="radio" name="status" value="draft" @checked($currentStatus === 'draft')>
                  <strong>Draft</strong>
                  <span>Hidden from visitors</span>
                </label>
                <label class="choice live {{ $currentStatus === 'published' ? 'is-on' : '' }}">
                  <input type="radio" name="status" value="published" @checked($currentStatus === 'published')>
                  <strong>Approved</strong>
                  <span>Visible on website</span>
                </label>
              </div>
              <div class="row-actions" style="margin-top:12px;flex-direction:column;align-items:stretch;">
                @if($isPublished)
                  <button class="btn warn" type="submit" form="unpublishForm">Hide page from website</button>
                @else
                  <button class="btn green" type="submit" form="approveForm">Approve &amp; publish now</button>
                @endif
              </div>
            @else
              <div class="publish-card">
                <span class="badge badge-live">Home page is always live</span>
              </div>
            @endif
          </div>
          <div class="rail-card">
            <h3>Quick reminder</h3>
            <ol>
              <li>Left list = website {{ $partName }} names (locked)</li>
              <li>Right box = content visitors read</li>
              <li>Select photo = upload from your PC</li>
              <li>Save = updates the live page (if Approved)</li>
            </ol>
          </div>
        </aside>
      </div>
    </div>
  </form>
  </div>

  <form id="clearContentForm" method="post" action="{{ route('admin.pages.destroy', $page['slug']) }}" onsubmit="return confirm('Clear all text on this page?');" style="display:none;">
    @csrf
    @method('DELETE')
  </form>
  @if(! $isHome)
    <form id="deletePageForm" method="post" action="{{ route('admin.pages.destroyPage', $page['slug']) }}" onsubmit="return confirm('Delete this page permanently?');" style="display:none;">
      @csrf
      @method('DELETE')
    </form>
    <form id="approveForm" method="post" action="{{ route('admin.pages.approve', $page['slug']) }}" style="display:none;">@csrf</form>
    <form id="unpublishForm" method="post" action="{{ route('admin.pages.unpublish', $page['slug']) }}" style="display:none;">@csrf</form>
  @endif

  @include('admin.partials.editor', ['selector' => 'textarea.page-section-body', 'height' => 360])
  <script>
    (function () {
      const pageTitle = @json($shortTitle);
      const items = document.querySelectorAll('[data-doc-target]');
      const panels = document.querySelectorAll('[data-doc-panel]');
      const nowLabel = document.querySelector('[data-now-label]');
      const nowHint = document.querySelector('[data-now-hint]');
      const activeSeg = document.querySelector('[data-active-seg]');
      const saveChip = document.querySelector('[data-save-chip]');

      function setActive(btn) {
        const id = btn.getAttribute('data-doc-target');
        const label = btn.getAttribute('data-doc-label') || 'Content';
        const hint = btn.getAttribute('data-doc-hint') || '';
        items.forEach((el) => el.classList.remove('is-on'));
        panels.forEach((el) => el.classList.remove('is-on'));
        btn.classList.add('is-on');
        document.getElementById(id)?.classList.add('is-on');
        if (nowLabel) nowLabel.textContent = label;
        if (nowHint) nowHint.textContent = hint;
        if (activeSeg) activeSeg.textContent = label;
        if (saveChip) saveChip.textContent = pageTitle + ' → ' + label;
      }

      items.forEach((btn) => {
        btn.addEventListener('click', () => setActive(btn));
      });

      document.querySelectorAll('.choice').forEach((label) => {
        label.addEventListener('click', () => {
          document.querySelectorAll('.choice').forEach((el) => el.classList.remove('is-on'));
          label.classList.add('is-on');
        });
      });
    })();
  </script>
@endsection
