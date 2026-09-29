@extends('admin.layout')
@section('title', 'Notices')
@section('top', 'Notices')
@section('content')
  <div class="panel">
    <div class="toolbar">
      <div>
        <h1>Notices &amp; Announcements</h1>
        <p class="help" style="margin-bottom:0;">Post university notices here. Click <strong>Add notice</strong> to publish a new one.</p>
      </div>
      <a class="btn lg" href="{{ route('admin.notices.create') }}">+ Add notice</a>
    </div>

    <form class="toolbar" method="get" action="{{ route('admin.notices.index') }}">
      <div class="search-box">
        <label for="q">Find notice</label>
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
                <a class="btn ghost" href="{{ route('admin.notices.edit', $item['id']) }}">Edit</a>
                <a class="btn ghost" href="{{ url('/'.$item['slug']) }}" target="_blank" rel="noopener">View</a>
                <form method="post" action="{{ route('admin.notices.destroy', $item['id']) }}" onsubmit="return confirm('Delete this notice?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn red" type="submit">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4">No notices found. Click Add notice.</td></tr>
        @endforelse
      </tbody>
    </table>

    @if($result['pages'] > 1)
      <div class="pager">
        @for($p = 1; $p <= min($result['pages'], 20); $p++)
          <a class="btn {{ $p === $result['page'] ? '' : 'ghost' }}" href="{{ route('admin.notices.index', ['page' => $p, 'q' => $result['q']]) }}">{{ $p }}</a>
        @endfor
        @if($result['pages'] > 20)
          <span class="muted">… {{ $result['pages'] }} pages</span>
        @endif
      </div>
    @endif
  </div>
@endsection
