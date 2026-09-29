@extends('admin.layout')
@section('title', 'Dashboard')
@section('top', 'Dashboard')
@section('content')
  <div class="welcome">
    <div>
      <h1>UU Admin Portal</h1>
      <p>Manage Uttara University website content from one central administration workspace.</p>
    </div>
    <div class="row-actions">
      <a class="btn xl" href="{{ route('admin.news.create') }}" style="background:#fff;color:var(--navy);"><i class="fa-solid fa-plus"></i> Add news</a>
      <a class="btn ghost xl" href="{{ route('admin.pages.index') }}" style="border-color:rgba(255,255,255,.35);color:#fff;">Edit pages</a>
    </div>
  </div>

  <div class="stats">
    <div class="stat"><strong>{{ number_format($stats['news']) }}</strong><span>News</span></div>
    <div class="stat"><strong>{{ number_format($stats['notices']) }}</strong><span>Notices</span></div>
    <div class="stat"><strong>{{ number_format($stats['pages']) }}</strong><span>Pages</span></div>
    <div class="stat"><strong>{{ number_format($stats['drafts']) }}</strong><span>Pending approval</span></div>
    <div class="stat"><strong>{{ number_format($stats['teachers']) }}</strong><span>Teachers</span></div>
  </div>

  @if(($stats['drafts'] ?? 0) > 0)
    <div class="status-bar" style="margin-bottom:20px;border-color:#fde68a;background:var(--warn-bg);">
      <div class="meta">
        <span class="badge badge-draft"><i class="fa-regular fa-clock"></i> {{ $stats['drafts'] }} page(s) waiting for approval</span>
      </div>
      <a class="btn green" href="{{ route('admin.pages.index', ['status' => 'draft']) }}">Review now</a>
    </div>
  @endif

  <p class="eyebrow">Where do I change…?</p>
  <div class="map-card">
    <div class="map-grid">
      <a class="map-item" href="{{ route('admin.news.index') }}">
        <strong>News stories</strong>
        <span>Homepage news + News &amp; Events pages</span>
      </a>
      <a class="map-item" href="{{ route('admin.notices.index') }}">
        <strong>Notices / announcements</strong>
        <span>Notice board and notice list pages</span>
      </a>
      <a class="map-item" href="{{ route('admin.pages.index') }}">
        <strong>Any other website page</strong>
        <span>About, departments, admission, etc.</span>
      </a>
      <a class="map-item" href="{{ route('admin.home.edit') }}">
        <strong>Homepage banners</strong>
        <span>Top slider photos and ticker text</span>
      </a>
      <a class="map-item" href="{{ route('admin.teachers.index') }}">
        <strong>Teacher profiles</strong>
        <span>Faculty photos, bio tabs, contact info</span>
      </a>
      <a class="map-item" href="{{ route('admin.menus.edit') }}">
        <strong>Top menus / footer links</strong>
        <span>What visitors click in the header</span>
      </a>
    </div>
  </div>

  <p class="eyebrow">Quick actions</p>
  <div class="grid-cards">
    <a class="action-card" href="{{ route('admin.news.create') }}">
      <div class="eyebrow">Most used</div>
      <h2>Post a news story</h2>
      <p>Share events and updates with a title, date, photo, and story.</p>
    </a>
    <a class="action-card" href="{{ route('admin.notices.create') }}">
      <div class="eyebrow">Most used</div>
      <h2>Post a notice</h2>
      <p>Publish exam, holiday, or office announcements quickly.</p>
    </a>
    <a class="action-card" href="{{ route('admin.pages.index') }}">
      <div class="eyebrow">Pages</div>
      <h2>Edit &amp; approve pages</h2>
      <p>Change any website page, then approve before it goes live.</p>
    </a>
    <a class="action-card" href="{{ route('admin.home.edit') }}">
      <div class="eyebrow">Homepage</div>
      <h2>Banners &amp; ticker</h2>
      <p>Update homepage slider images and scrolling messages.</p>
    </a>
    <a class="action-card" href="{{ route('admin.menus.edit') }}">
      <div class="eyebrow">Navigation</div>
      <h2>Menus &amp; links</h2>
      <p>Control header menus, quick links, and footer columns.</p>
    </a>
    <a class="action-card" href="{{ route('admin.settings.edit') }}">
      <div class="eyebrow">Site-wide</div>
      <h2>Settings</h2>
      <p>Phone numbers, logos, social icons, and copyright text.</p>
    </a>
  </div>

  <div class="two-col">
    <section class="form-section">
      <div class="form-section-head">
        <div>
          <p class="eyebrow">Recent</p>
          <h2>Latest news</h2>
        </div>
        <a class="btn ghost sm" href="{{ route('admin.news.index') }}">View all</a>
      </div>
      <div class="form-section-body" style="padding-top:0;">
        <table>
          <tbody>
            @forelse($recentNews as $item)
              <tr>
                <td class="title-cell"><a href="{{ route('admin.news.edit', $item['id']) }}">{{ $item['title'] }}</a></td>
                <td class="muted">{{ $item['display_date'] }}</td>
              </tr>
            @empty
              <tr><td class="muted">No news yet. <a href="{{ route('admin.news.create') }}">Add the first story</a>.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="form-section">
      <div class="form-section-head">
        <div>
          <p class="eyebrow">Approval</p>
          <h2>Pages waiting</h2>
        </div>
        <a class="btn ghost sm" href="{{ route('admin.pages.index', ['status' => 'draft']) }}">View all</a>
      </div>
      <div class="form-section-body" style="padding-top:0;">
        <table>
          <tbody>
            @forelse(array_slice($draftPages ?? [], 0, 8) as $item)
              <tr>
                <td class="title-cell"><a href="{{ route('admin.pages.edit', $item['slug']) }}">{{ $item['short_title'] ?? $item['title'] }}</a></td>
                <td>
                  <form method="post" action="{{ route('admin.pages.approve', $item['slug']) }}" style="margin:0;">
                    @csrf
                    <button class="btn green sm" type="submit">Approve</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td class="muted">All clear — no drafts waiting.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection
