@extends('admin.layout')
@section('title', 'Website settings')
@section('top', 'Settings')
@section('content')
  @php $s = $settings ?? []; $socials = $s['socials'] ?? []; @endphp
  <div class="panel">
    <h1>Website settings</h1>
    <p class="help">Phone number, logos, social media links, and copyright text used across the whole site.</p>
    <form method="post" action="{{ route('admin.settings.update') }}">
      @csrf
      @method('PUT')
      <label>Phone text shown in header</label>
      <input type="text" name="phone_display" value="{{ old('phone_display', $s['phone_display'] ?? '') }}" required>
      <label>Phone dial number</label>
      <input type="text" name="phone_tel" value="{{ old('phone_tel', $s['phone_tel'] ?? '') }}" required>
      <label>Logo image URL</label>
      <input type="text" name="logo_url" value="{{ old('logo_url', $s['logo_url'] ?? '') }}">
      <label>Mobile logo image URL</label>
      <input type="text" name="mobile_logo_url" value="{{ old('mobile_logo_url', $s['mobile_logo_url'] ?? '') }}">
      <label>Copyright line</label>
      <input type="text" name="copyright" value="{{ old('copyright', $s['copyright'] ?? '') }}" required>

      <h3 style="margin:28px 0 8px;color:#0a1f3d;">Social media</h3>
      @for($i = 0; $i < max(count($socials), 6); $i++)
        <div style="display:grid;grid-template-columns:1fr 1.4fr .7fr;gap:8px;margin-bottom:8px;">
          <input type="text" name="social_label[]" value="{{ $socials[$i]['label'] ?? '' }}" placeholder="Facebook">
          <input type="text" name="social_url[]" value="{{ $socials[$i]['url'] ?? '' }}" placeholder="https://…">
          <input type="text" name="social_icon[]" value="{{ $socials[$i]['icon'] ?? '' }}" placeholder="facebook-square">
        </div>
      @endfor
      <p class="field-note">Icon names use Font Awesome brands (facebook-square, youtube, linkedin, …).</p>
      <div class="actions"><button class="btn lg" type="submit">Save settings</button></div>
    </form>
  </div>
@endsection
