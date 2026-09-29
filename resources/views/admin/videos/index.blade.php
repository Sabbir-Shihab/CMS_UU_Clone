@extends('admin.layout')
@section('title', 'Videos')
@section('top', 'Video gallery')
@section('content')
  <div class="panel">
    <h1>Video gallery</h1>
    <p class="help">Add YouTube videos. Paste either the video ID or the full YouTube link.</p>

    <form method="post" action="{{ route('admin.videos.store') }}" class="toolbar" style="align-items:end;">
      @csrf
      <div style="flex:1;min-width:200px;">
        <label>Title</label>
        <input type="text" name="title" required placeholder="Welcome to Uttara University">
      </div>
      <div style="flex:1;min-width:200px;">
        <label>YouTube ID or URL</label>
        <input type="text" name="id" required placeholder="https://www.youtube.com/watch?v=…">
      </div>
      <button class="btn" type="submit">+ Add video</button>
    </form>

    <table>
      <thead><tr><th>Preview</th><th>Title</th><th>ID</th><th></th></tr></thead>
      <tbody>
        @forelse($videos as $video)
          <tr>
            <td><img class="thumb" src="/uploads/youtube/{{ $video['id'] }}.jpg" alt=""></td>
            <td class="title-cell">{{ $video['title'] }}</td>
            <td class="muted">{{ $video['id'] }}</td>
            <td>
              <form method="post" action="{{ route('admin.videos.destroy', $video['id']) }}" onsubmit="return confirm('Remove this video?');">
                @csrf
                @method('DELETE')
                <button class="btn red" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4">No videos yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
