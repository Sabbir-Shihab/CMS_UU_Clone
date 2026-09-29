@extends('admin.layout')
@section('title', 'News & Events')
@section('top', 'News & Events')
@section('content')
  <div class="panel">
    <div class="toolbar">
      <div>
        <h1>News &amp; Events</h1>
        <p class="help" style="margin-bottom:0;">Publish campus news. Click <strong>Add news</strong> to start. Search by title if you need an older story.</p>
      </div>
      <a class="btn lg" href="{{ route('admin.news.create') }}">+ Add news</a>
    </div>

    <form class="toolbar" method="get" action="{{ route('admin.news.index') }}">
      <div class="search-box">
        <label for="q">Find news</label>
        <input id="q" type="search" name="q" value="{{ $result['q'] }}" placeholder="Type a word from the title…">
      </div>
      <button class="btn ghost" type="submit">Search</button>
    </form>

    <p class="muted">Showing {{ count($result['items']) }} of {{ number_format($result['total']) }}</p>

    <table>
      <thead>
        <tr>
          <th>Photo</th>
          <th>Title</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($result['items'] as $item)
          <tr>
            <td>
              @if(!empty($item['image_path']))
                <img class="thumb" src="{{ \App\Support\AdminPostHelper::publicPath($item['image_path']) }}" alt="">
              @endif
            </td>
            <td class="title-cell">{{ $item['title'] }}</td>
            <td class="muted">{{ $item['display_date'] }}</td>
            <td>
              <div class="row-actions">
                <a class="btn ghost" href="{{ route('admin.news.edit', $item['id']) }}">Edit</a>
                <a class="btn ghost" href="{{ url('/'.$item['slug']) }}" target="_blank" rel="noopener">View</a>
                <form method="post" action="{{ route('admin.news.destroy', $item['id']) }}" onsubmit="return confirm('Delete this news story?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn red" type="submit">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4">No news found. Click Add news.</td></tr>
        @endforelse
      </tbody>
    </table>

    @if($result['pages'] > 1)
      <div class="pager">
        @for($p = 1; $p <= $result['pages']; $p++)
          <a class="btn {{ $p === $result['page'] ? '' : 'ghost' }}" href="{{ route('admin.news.index', ['page' => $p, 'q' => $result['q']]) }}">{{ $p }}</a>
        @endfor
      </div>
    @endif
  </div>
@endsection
