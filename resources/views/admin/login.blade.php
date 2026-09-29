@extends('admin.layout')
@section('title', 'Sign in')
@section('content')
<div class="login-wrap">
  <div class="login-card">
    <img src="/wp-content/themes/utheme/images/UULogo.webp" alt="Uttara University">
    <h1>UU Admin</h1>
    <p class="help">Sign in to publish news, notices, and update website pages.</p>
    <form method="post" action="{{ route('admin.login.submit') }}">
      @csrf
      <label>Email</label>
      <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="your@email.com">
      @error('email')<p class="error">{{ $message }}</p>@enderror
      <label>Password</label>
      <input type="password" name="password" required autocomplete="current-password">
      <p class="actions"><button class="btn xl block" type="submit">Sign in</button></p>
    </form>
  </div>
</div>
@endsection
