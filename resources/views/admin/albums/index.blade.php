@extends('admin.layout')
@section('title', 'Photo albums')
@section('top', 'Albums')
@section('content')
  <div class="panel">
    <div class="toolbar">
      <div>
        <h1>Convocation photo albums</h1>
        <p class="help" style="margin-bottom:0;">Upload photo albums for Convocations gallery.</p>
      </div>
      <a class="btn lg" href="{{ route('admin.albums.create') }}">+ New album</a>
    </div>
    <table>
      <thead><tr><th>Title</th><th>Photos</th><th></th></tr></thead>
      <tbody>
        @forelse($albums as $album)
          <tr>
            <td class="title-cell">{{ $album['title'] ?? '' }}</td>
            <td class="muted">{{ count($album['photos'] ?? []) }}</td>
            <td>
              <div class="row-actions">
                <a class="btn ghost" href="{{ route('admin.albums.edit', $album['id']) }}">Edit</a>
                <form method="post" action="{{ route('admin.albums.destroy', $album['id']) }}" onsubmit="return confirm('Delete this album?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn red" type="submit">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="3">No albums yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
