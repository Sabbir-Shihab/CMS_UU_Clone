@extends('admin.layout')
@section('title', $teacher ? 'Edit teacher' : 'Add teacher')
@section('top', $teacher ? 'Edit teacher' : 'Add teacher')
@section('content')
  @php
    $profile = $profile ?? ['heading' => '', 'title' => '', 'roles' => [], 'social' => [], 'tabs' => []];
    $tabDefinitions = $tabDefinitions ?? \App\Support\TeacherRepository::PROFILE_TABS;
  @endphp

  <div class="crumb">
    <a href="{{ route('admin.teachers.index', ['department' => $selected]) }}"><i class="fa-solid fa-arrow-left"></i> Teachers list</a>
    <span class="sep">/</span>
    <span>{{ $teacher['name'] ?? 'New' }}</span>
  </div>

  <div class="editor-hero">
    <div>
      <p class="eyebrow">Faculty / Staff</p>
      <h1>{{ $teacher ? $teacher['name'] : 'Add a new teacher' }}</h1>
      <p class="sub">Save basic details, then fill the tabs below and turn on <strong>Show on profile</strong>.</p>
    </div>
    @if($teacher)
      <a class="btn ghost xl" href="{{ url('/faculty/'.$teacher['id']) }}" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> View profile</a>
    @endif
  </div>

  <div class="callout">
    <i class="fa-solid fa-circle-info"></i>
    <div>
      <strong>How it works</strong>
      <span>Add content in each section → turn on <strong>Show on profile</strong> → click Save. Only active tabs appear on the public profile.</span>
    </div>
  </div>

  <form method="post" action="{{ $teacher ? route('admin.teachers.update', $teacher['id']) : route('admin.teachers.store') }}" enctype="multipart/form-data" class="form-stack" id="teacherForm">
    @csrf
    @if($teacher) @method('PUT') @endif

    <section class="form-section">
      <div class="form-section-head">
        <div>
          <p class="eyebrow">Basic info</p>
          <h2>Name, designation &amp; contact</h2>
        </div>
      </div>
      <div class="form-section-body field-grid">
        <div class="field span-2">
          <label>Department / office</label>
          <select name="department_id" required>
            @foreach($departments as $item)
              <option value="{{ $item['id'] }}" @selected((string) old('department_id', $teacher['department_id'] ?? '') === (string) $item['id'] || ($selected === $item['dept_key'] && !$teacher))>{{ $item['name'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Full name</label>
          <input type="text" name="name" value="{{ old('name', $teacher['name'] ?? '') }}" required>
          @error('name')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
          <label>Designation</label>
          <input type="text" name="designation" value="{{ old('designation', $teacher['designation'] ?? '') }}" placeholder="Professor / Lecturer">
        </div>
        <div class="field">
          <label>Education line 1</label>
          <input type="text" name="institute1" value="{{ old('institute1', $teacher['institute1'] ?? '') }}">
        </div>
        <div class="field">
          <label>Education line 2</label>
          <input type="text" name="institute2" value="{{ old('institute2', $teacher['institute2'] ?? '') }}">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" value="{{ old('email', $teacher['email'] ?? '') }}">
        </div>
        <div class="field">
          <label>Office extension</label>
          <input type="text" name="office_ext" value="{{ old('office_ext', $teacher['office_ext'] ?? '') }}">
        </div>
        <div class="field span-2">
          <label>Photo</label>
          @if(!empty($teacher['photo_path']))
            <p><img class="teacher-thumb" src="{{ str_starts_with($teacher['photo_path'], 'http') ? $teacher['photo_path'] : asset($teacher['photo_path']) }}" alt=""></p>
          @endif
          <div class="upload-box">
            <input type="file" name="photo" accept="image/*">
            <p class="field-note">Optional. JPG/PNG, up to 4 MB.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="form-section">
      <div class="form-section-head">
        <div>
          <p class="eyebrow">Profile header</p>
          <h2>Top of the profile page</h2>
          <p>Leave blank to use name and designation automatically.</p>
        </div>
      </div>
      <div class="form-section-body field-grid">
        <div class="field">
          <label>Banner heading</label>
          <input type="text" name="profile_heading" value="{{ old('profile_heading', $profile['heading']) }}" placeholder="Example: Dr. Rahman's Profile">
        </div>
        <div class="field">
          <label>Profile title</label>
          <input type="text" name="profile_title" value="{{ old('profile_title', $profile['title']) }}" placeholder="Full display name">
        </div>
        <div class="field">
          <label>Role lines (one per line)</label>
          <textarea name="profile_roles" rows="4" placeholder="Professor&#10;Department of CSE">{{ old('profile_roles', implode("\n", $profile['roles'])) }}</textarea>
        </div>
        <div class="field">
          <label>Social links (one URL per line)</label>
          <textarea name="profile_social" rows="4" placeholder="https://scholar.google.com/...">{{ old('profile_social', implode("\n", $profile['social'])) }}</textarea>
        </div>
      </div>
    </section>

    <section class="form-section">
      <div class="form-section-head">
        <div>
          <p class="eyebrow">Profile tabs</p>
          <h2>Education, Publications, Experience…</h2>
          <p>A tab appears on the profile only when Show on profile is turned on.</p>
        </div>
      </div>
      <div class="form-section-body teacher-profile-tabs-body">
        <div class="teacher-tab-editor">
          @foreach($tabDefinitions as $key => $label)
            @php
              $tab = $profile['tabs'][$key] ?? ['label' => $label, 'html' => '', 'active' => false];
              $submittedTabs = old('tab_active');
              $isActive = is_array($submittedTabs)
                  ? ! empty($submittedTabs[$key])
                  : (bool) ($tab['active'] ?? false);
              $html = old('tab.'.$key, $tab['html'] ?? '');
            @endphp
            <details class="tab-block {{ $key === 'educational' ? 'tab-block-featured' : ' ' }}" @if($key === 'educational' || $loop->first) open @endif>
              <summary>
                <span class="tab-block-title">
                  @if($key === 'educational')
                    <span class="tab-block-kicker"><i class="fa-solid fa-graduation-cap"></i> Main profile section</span>
                  @endif
                  <strong>{{ $label }}</strong>
                  <small>{{ $isActive ? 'Visible on profile' : 'Hidden' }}</small>
                </span>
                <label class="tab-switch" onclick="event.stopPropagation();">
                  <input type="checkbox" name="tab_active[{{ $key }}]" value="1" @checked($isActive)>
                  <span>Show on profile</span>
                </label>
              </summary>
              <div class="tab-block-body">
                <label for="tab-{{ $key }}">{{ $label }} details</label>
                @if($key === 'educational')
                  <p class="tab-editor-intro">Add qualifications in a clean table or as a list. The content below is shown on the public profile when this section is active.</p>
                @endif
                <div class="photo-chooser">
                  <button type="button" class="btn" data-choose-photo="tab-{{ $key }}">
                    <i class="fa-regular fa-image"></i> Choose photo
                  </button>
                  <div class="copy">
                    <strong>Add a picture</strong>
                    <span>Click Choose photo → pick from your computer.</span>
                    <span data-photo-status></span>
                  </div>
                </div>
                <div class="editor-frame">
                  <textarea id="tab-{{ $key }}" class="teacher-tab-body" name="tab[{{ $key }}]" rows="10">{{ $html }}</textarea>
                </div>
                <p class="field-note">You can add lists, headings, and links. Use the image button in the toolbar for photos.</p>
              </div>
            </details>
          @endforeach
        </div>
      </div>
    </section>

    <div class="sticky-save">
      <div class="hint">Saving stores basic info and all tabs together.</div>
      <div class="row-actions">
        <button class="btn xl" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save</button>
        @if($teacher)
          <a class="btn ghost xl" href="{{ url('/faculty/'.$teacher['id']) }}" target="_blank" rel="noopener">Open profile</a>
        @endif
      </div>
    </div>
  </form>

  @include('admin.partials.editor', ['selector' => 'textarea.teacher-tab-body', 'height' => 280])
@endsection
