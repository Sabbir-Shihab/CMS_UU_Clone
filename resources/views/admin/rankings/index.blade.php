@extends('admin.layout')
@section('title', 'Ranking logos')
@section('top', 'Rankings')
@section('content')
  <div class="panel">
    <h1>Ranking &amp; recognition logos</h1>
    <p class="help">These logos appear on the homepage under “Recognitions and Rankings”.</p>

    <form method="post" action="{{ route('admin.rankings.store') }}" enctype="multipart/form-data" class="toolbar" style="align-items:end;">
      @csrf
      <div style="flex:1;min-width:180px;">
        <label>Name</label>
        <input type="text" name="alt" required placeholder="QS Asia Ranking">
      </div>
      <div style="flex:1;min-width:180px;">
        <label>Logo image</label>
        <input type="file" name="image" accept="image/*" required>
      </div>
      <button class="btn" type="submit">+ Add logo</button>
    </form>

    <table>
      <thead><tr><th>Logo</th><th>Name</th><th></th></tr></thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td><img class="thumb" src="{{ str_starts_with($item['image'] ?? '', 'http') ? $item['image'] : asset(ltrim($item['image'] ?? '', '/')) }}" alt=""></td>
            <td class="title-cell">{{ $item['alt'] ?? '' }}</td>
            <td>
              <form method="post" action="{{ route('admin.rankings.destroy', $item['id']) }}" onsubmit="return confirm('Remove this logo?');">
                @csrf
                @method('DELETE')
                <button class="btn red" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="3">No logos yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
