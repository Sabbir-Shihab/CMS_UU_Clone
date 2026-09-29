@extends('admin.layout')
@section('title', 'Menus')
@section('top', 'Menus')
@section('content')
  @php
    $menus = $menus ?? [];
    $section = request('section', 'quick_links');
  @endphp
  <div class="panel">
    <h1>Menus &amp; links</h1>
    <p class="help">Change the top quick links, main menu, and footer links. Use a page address like <code>/about</code> or a full https:// link.</p>

    <div class="toolbar">
      <a class="btn {{ $section==='quick_links'?'':'ghost' }}" href="{{ route('admin.menus.edit', ['section'=>'quick_links']) }}">Top quick links</a>
      <a class="btn {{ $section==='main_nav'?'':'ghost' }}" href="{{ route('admin.menus.edit', ['section'=>'main_nav']) }}">Main menu</a>
      <a class="btn {{ $section==='footer_columns'?'':'ghost' }}" href="{{ route('admin.menus.edit', ['section'=>'footer_columns']) }}">Footer</a>
    </div>

    @if($section === 'footer_columns')
      <form method="post" action="{{ route('admin.menus.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="section" value="footer_columns">
        @foreach(($menus['footer_columns'] ?? []) as $i => $col)
          <div style="border:1px solid #d7e0ec;border-radius:12px;padding:14px;margin-bottom:14px;">
            <label>Column title</label>
            <input type="text" name="footer_title[{{ $i }}]" value="{{ $col['title'] ?? '' }}">
            <p class="field-note">Links in this column (label + URL). Leave a row blank to skip.</p>
            @for($j = 0; $j < max(count($col['links'] ?? []), 6); $j++)
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:6px;">
                <input type="text" name="footer_label[{{ $i }}][]" value="{{ $col['links'][$j]['label'] ?? '' }}" placeholder="Label">
                <input type="text" name="footer_url[{{ $i }}][]" value="{{ $col['links'][$j]['url'] ?? '' }}" placeholder="/page or https://…">
              </div>
            @endfor
          </div>
        @endforeach
        <div class="actions"><button class="btn lg" type="submit">Save footer</button></div>
      </form>
    @else
      <form method="post" action="{{ route('admin.menus.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="section" value="{{ $section }}">
        @foreach(($menus[$section] ?? []) as $i => $item)
          <div style="border:1px solid #d7e0ec;border-radius:12px;padding:14px;margin-bottom:14px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <div>
                <label>Menu label</label>
                <input type="text" name="label[{{ $i }}]" value="{{ $item['label'] ?? '' }}">
              </div>
              <div>
                <label>URL</label>
                <input type="text" name="url[{{ $i }}]" value="{{ $item['url'] ?? '' }}">
              </div>
            </div>
            @if($section === 'main_nav')
              <label style="font-weight:500;"><input type="checkbox" name="mega[{{ $i }}]" value="1" @checked(!empty($item['mega']))> Wide mega menu</label>
              <input type="hidden" name="nav[{{ $i }}]" value="{{ $item['nav'] ?? '' }}">
            @endif
            @if($section === 'quick_links')
              <input type="hidden" name="class[{{ $i }}]" value="{{ $item['class'] ?? '' }}">
              <input type="hidden" name="li_class[{{ $i }}]" value="{{ $item['li_class'] ?? '' }}">
            @endif
            <p class="field-note" style="margin-top:10px;">Dropdown children (optional)</p>
            @for($j = 0; $j < max(count($item['children'] ?? []), 4); $j++)
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:6px;">
                <input type="text" name="child_label[{{ $i }}][]" value="{{ $item['children'][$j]['label'] ?? '' }}" placeholder="Child label">
                <input type="text" name="child_url[{{ $i }}][]" value="{{ $item['children'][$j]['url'] ?? '' }}" placeholder="Child URL">
              </div>
            @endfor
          </div>
        @endforeach

        {{-- spare empty rows to add new items --}}
        @for($i = count($menus[$section] ?? []); $i < count($menus[$section] ?? []) + 2; $i++)
          <div style="border:1px dashed #b7c7de;border-radius:12px;padding:14px;margin-bottom:14px;">
            <p class="field-note">Add new item</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <input type="text" name="label[{{ $i }}]" placeholder="New label">
              <input type="text" name="url[{{ $i }}]" placeholder="/new-page">
            </div>
            @for($j = 0; $j < 2; $j++)
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px;">
                <input type="text" name="child_label[{{ $i }}][]" placeholder="Child label">
                <input type="text" name="child_url[{{ $i }}][]" placeholder="Child URL">
              </div>
            @endfor
          </div>
        @endfor

        <div class="actions"><button class="btn lg" type="submit">Save menu</button></div>
      </form>
    @endif
  </div>
@endsection
