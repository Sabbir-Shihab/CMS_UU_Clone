@extends('admin.layout')
@section('title', 'Pages')
@section('top', 'Website pages')
@section('content')
  <div class="editor-hero">
    <div>
      <p class="eyebrow">Find → Edit → Save → Approve</p>
      <h1>Website pages</h1>
      <p class="sub">Search for the page you want to change. Opening Edit shows exactly where that content appears on the website.</p>
    </div>
    <a class="btn xl" href="{{ route('admin.pages.create') }}"><i class="fa-solid fa-plus"></i> New page</a>
  </div>

  <div class="how-steps">
    <div class="how-step">
      <span class="n">1</span>
      <div><strong>Find the page</strong><span>Use search or browse by group below</span></div>
    </div>
    <div class="how-step">
      <span class="n">2</span>
      <div><strong>Open Edit</strong><span>Update the tab/section you need</span></div>
    </div>
    <div class="how-step">
      <span class="n">3</span>
      <div><strong>Save &amp; approve</strong><span>Draft stays private until Approved</span></div>
    </div>
  </div>

  <div class="callout">
    <i class="fa-solid fa-circle-info"></i>
    <div>
      <strong>What “Live” and “Draft” mean</strong>
      <span><strong>Live / Approved</strong> = visitors can open the page. <strong>Draft</strong> = hidden until you approve. Home page is always live.</span>
    </div>
  </div>

  <div class="big-search" style="margin-bottom:14px;">
    <input id="pageFilter" type="search" placeholder="Type a page name… e.g. About, CSE, Admission, Notice">
  </div>

  <div class="filter-tabs">
    <a class="{{ ($statusFilter ?? '') === '' ? 'is-on' : '' }}" href="{{ route('admin.pages.index') }}">All ({{ $totalCount }})</a>
    <a class="{{ ($statusFilter ?? '') === 'published' ? 'is-on' : '' }}" href="{{ route('admin.pages.index', ['status' => 'published']) }}">Live on website</a>
    <a class="{{ ($statusFilter ?? '') === 'draft' ? 'is-on' : '' }}" href="{{ route('admin.pages.index', ['status' => 'draft']) }}">
      Waiting for approval
      @if(($draftCount ?? 0) > 0) ({{ $draftCount }}) @endif
    </a>
  </div>

  <div id="pageGroups">
    @forelse(($groups ?? []) as $groupName => $items)
      <section class="page-group" data-group>
        <div class="page-group-head">
          <h2>{{ $groupName }}</h2>
          <span>{{ count($items) }} page{{ count($items) === 1 ? '' : 's' }} · same area on the website</span>
        </div>
        <div class="page-rows">
          @foreach($items as $item)
            @php
              $live = ($item['status'] ?? 'published') === 'published';
              $path = ($item['slug'] ?? '') === 'home' ? '/' : '/'.($item['slug'] ?? '');
              $name = $item['short_title'] ?? $item['title'];
            @endphp
            <article class="page-row" data-name="{{ strtolower($name.' '.$item['slug'].' '.$groupName) }}">
              <div>
                <div class="title-cell">{{ $name }}</div>
                <div class="where-line">
                  <span>Shows on website at</span>
                  <code>{{ $path }}</code>
                </div>
                <div class="meta-chips" style="margin-top:8px;">
                  @if($live)
                    <span class="badge badge-live"><i class="fa-solid fa-check"></i> Live</span>
                  @else
                    <span class="badge badge-draft"><i class="fa-regular fa-clock"></i> Draft</span>
                  @endif
                  <span class="meta-chip">{{ $groupName }}</span>
                </div>
              </div>
              <div class="row-actions">
                <a class="btn" href="{{ route('admin.pages.edit', $item['slug']) }}"><i class="fa-regular fa-pen-to-square"></i> Edit content</a>
                @if(! in_array($item['slug'], ['home', 'index'], true))
                  @if(! $live)
                    <form method="post" action="{{ route('admin.pages.approve', $item['slug']) }}" style="margin:0;">
                      @csrf
                      <button class="btn green" type="submit">Make live</button>
                    </form>
                  @else
                    <form method="post" action="{{ route('admin.pages.unpublish', $item['slug']) }}" style="margin:0;">
                      @csrf
                      <button class="btn warn" type="submit">Hide</button>
                    </form>
                  @endif
                @endif
                <a class="btn ghost" href="{{ ($item['slug'] ?? '') === 'home' ? url('/') : url('/'.$item['slug']) }}" target="_blank" rel="noopener">View page</a>
              </div>
            </article>
          @endforeach
        </div>
      </section>
    @empty
      <div class="panel"><p class="muted" style="margin:0;">No pages found for this filter.</p></div>
    @endforelse
  </div>
  <p id="noPageMatch" class="muted" style="display:none;margin-top:8px;">No pages match that search. Try another word.</p>

  <script>
    const filter = document.getElementById('pageFilter');
    const empty = document.getElementById('noPageMatch');
    filter?.addEventListener('input', () => {
      const q = filter.value.toLowerCase().trim();
      let shown = 0;
      document.querySelectorAll('[data-group]').forEach((group) => {
        let groupShown = 0;
        group.querySelectorAll('.page-row').forEach((el) => {
          const ok = !q || (el.dataset.name || '').includes(q);
          el.style.display = ok ? '' : 'none';
          if (ok) groupShown += 1;
        });
        group.style.display = groupShown ? '' : 'none';
        shown += groupShown;
      });
      if (empty) empty.style.display = shown ? 'none' : '';
    });
    filter?.focus();
  </script>
@endsection
