<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $teacher['name'] }} | Uttara University</title>
  <link rel="icon" href="/wp-content/themes/utheme/images/UULogo.webp">
  <link rel="stylesheet" href="{{ asset('vendor/fonts/teacher-fonts.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=77">
</head>
<body data-cms="1" data-stack="1" data-page="academics">
  @include('partials.header')
  @php
    $photo = $teacher['photo_path'] ?? '';
    if ($photo && !str_starts_with($photo, 'http') && !str_starts_with($photo, 'data:')) {
        $photo = asset($photo);
    }
    $stored = $profile['tabs'] ?? [];
    $visibleTabs = [];
    foreach (\App\Support\TeacherRepository::PROFILE_TABS as $key => $label) {
        $tab = $stored[$key] ?? [];
        $active = array_key_exists('active', $tab)
            ? (bool) $tab['active']
            : trim((string) ($tab['html'] ?? '')) !== '';
        if (! $active) {
            continue;
        }
        $visibleTabs[$key] = $tab['label'] ?? $label;
    }
  @endphp
  <div class="convocation-banner"><div class="container">
    <h3 class="dept-name-common">{{ $profile['heading'] ?: ($teacher['name']."'s Profile") }}</h3>
  </div></div>
  <main class="page-wrap teacher-profile-page">
    <div class="container">
      <div class="teacher-profile-top">
        @if($photo)
          <img class="photo" src="{{ $photo }}" alt="{{ $teacher['name'] }}">
        @endif
        <div class="teacher-profile-meta">
          <div class="page-title-heading">
            <h2 class="title mb-3">{{ $profile['title'] ?: $teacher['name'] }}</h2>
          </div>
          @foreach(($profile['roles'] ?? []) as $role)
            <p class="teacher-profile-role">{{ $role }}</p>
          @endforeach
          @if(empty($profile['roles']) && $teacher['designation'])
            <p class="teacher-profile-role">{{ $teacher['designation'] }}, {{ $teacher['department_name'] }}</p>
          @endif
          <div class="contact">
            @if($teacher['email'])
              <p><a href="mailto:{{ $teacher['email'] }}"><strong><i class="fa-solid fa-envelope"></i> {{ $teacher['email'] }}</strong></a></p>
            @endif
            @if($teacher['office_ext'])
              <p class="ext"><strong><i class="fa-solid fa-phone"></i> Extension Number: {{ $teacher['office_ext'] }}</strong></p>
            @endif
          </div>
          @if(!empty($profile['social']))
            <p class="teacher-profile-social">
              @foreach($profile['social'] as $href)
                <a class="icon" href="{{ $href }}" target="_blank" rel="noopener"><i class="fa-brands {{ str_contains($href, 'researchgate') ? 'fa-researchgate' : 'fa-google-scholar' }}"></i></a>
              @endforeach
            </p>
          @endif
        </div>
      </div>

      @if($visibleTabs === [])
        <p class="faculty-status">Profile details will appear here once sections are published.</p>
      @else
        <div data-tabs>
          <div class="dept-tab-bg teacher-profile-tabs">
            <div class="dept-tabs">
              @foreach($visibleTabs as $key => $label)
                <button type="button" data-tab="{{ $key }}" class="{{ $loop->first ? 'is-active' : '' }}">{{ $label }}</button>
              @endforeach
            </div>
          </div>
          <div class="department-tab-content">
            @foreach($visibleTabs as $key => $label)
              @php $html = trim($stored[$key]['html'] ?? ''); @endphp
              <div class="panel {{ $loop->first ? 'is-active' : '' }}" data-panel="{{ $key }}">
                <div class="teacher-tab-html">
                  @if($html !== '')
                    {!! $html !!}
                  @else
                    <p class="faculty-status">No {{ strtolower($label) }} information is published yet.</p>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </main>
  @include('partials.footer')
  <script src="{{ asset('js/main.js') }}?v=46"></script>
</body>
</html>
