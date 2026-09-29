@extends('admin.layout')
@section('title', 'Teachers')
@section('top', 'Teachers & staff')
@section('content')
  <div class="editor-hero">
    <div>
      <p class="eyebrow">Faculty &amp; staff</p>
      <h1>Manage teachers &amp; staff</h1>
      <p class="sub">Choose a department → edit → fill Education / Publications tabs → turn on Show on profile.</p>
    </div>
    <a class="btn xl" href="{{ route('admin.teachers.create', ['department' => $department['dept_key'] ?? '']) }}"><i class="fa-solid fa-plus"></i> Add new</a>
  </div>

  <form method="get" action="{{ route('admin.teachers.index') }}" class="toolbar">
    <div class="search-box" style="max-width:none;flex:1;">
      <label>Department / office</label>
      <select name="department" onchange="this.form.submit()">
        @foreach($departments as $item)
          <option value="{{ $item['dept_key'] }}" @selected(($department['dept_key'] ?? '') === $item['dept_key'])>{{ $item['name'] }}</option>
        @endforeach
      </select>
    </div>
  </form>

  <div class="page-rows" style="margin-top:8px;">
    @forelse($teachers as $teacher)
      @php
        $profile = app(\App\Support\TeacherRepository::class)->decodeProfile($teacher);
        $activeCount = count(array_filter($profile['tabs'], fn ($tab) => ! empty($tab['active'])));
      @endphp
      <article class="page-row">
        <div style="display:flex;gap:12px;align-items:center;">
          @if(!empty($teacher['photo_path']))
            <img class="teacher-thumb" src="{{ str_starts_with($teacher['photo_path'], 'http') ? $teacher['photo_path'] : asset($teacher['photo_path']) }}" alt="">
          @endif
          <div>
            <div class="title-cell">{{ $teacher['name'] }}</div>
            <div class="meta-chips" style="margin-top:6px;">
              <span class="meta-chip">{{ $teacher['designation'] ?: 'No designation' }}</span>
              <span class="meta-chip">{{ $activeCount }} active tab(s)</span>
            </div>
          </div>
        </div>
        <div class="row-actions">
          <a class="btn" href="{{ route('admin.teachers.edit', $teacher['id']) }}"><i class="fa-regular fa-pen-to-square"></i> Edit / tabs</a>
          <a class="btn ghost" href="{{ url('/faculty/'.$teacher['id']) }}" target="_blank" rel="noopener">Profile</a>
          <form method="post" action="{{ route('admin.teachers.destroy', $teacher['id']) }}" onsubmit="return confirm('Remove this teacher?');" style="margin:0;">
            @csrf
            @method('DELETE')
            <button class="btn red" type="submit">Delete</button>
          </form>
        </div>
      </article>
    @empty
      <div class="panel"><p class="muted" style="margin:0;">No one in this department yet. <a href="{{ route('admin.teachers.create', ['department' => $department['dept_key'] ?? '']) }}">Add new</a></p></div>
    @endforelse
  </div>
@endsection
