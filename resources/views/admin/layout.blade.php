<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') | UU Admin</title>
  <link rel="icon" href="{{ asset('wp-content/uploads/2023/07/black-uu-logo.png') }}">
  <link rel="stylesheet" href="{{ asset('vendor/fonts/admin-fonts.css') }}?v=1">
  <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}?v=1">
  @yield('head')
  <style>
    :root {
      --navy: #0b4aa2;
      --navy-deep: #07357a;
      --ink: #0c1b33;
      --line: #e2e8f0;
      --bg: #f1f5f9;
      --soft: #f8fafc;
      --ok: #047857;
      --ok-bg: #ecfdf5;
      --warn: #b45309;
      --warn-bg: #fffbeb;
      --danger: #b91c1c;
      --danger-bg: #fef2f2;
      --muted: #64748b;
      --radius: 12px;
      --shadow: 0 1px 2px rgba(15, 23, 42, .06), 0 8px 24px rgba(15, 23, 42, .06);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; min-height: 100%; }
    body {
      font-family: "Source Sans 3", "Segoe UI", sans-serif;
      background: var(--bg);
      color: #1e293b;
      font-size: 15px;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }
    a { color: var(--navy); }
    button, input, select, textarea { font: inherit; }

    .login-wrap {
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
      padding: 24px; background: linear-gradient(145deg, #0c1b33 0%, #0b4aa2 55%, #1d4ed8 100%);
    }
    .login-card {
      width: 100%; max-width: 420px; background: #fff; border-radius: 16px;
      padding: 36px 32px; box-shadow: 0 24px 60px rgba(0,0,0,.28);
    }
    .login-card img { height: 52px; }
    .login-card h1 { margin: 18px 0 8px; font-size: 24px; color: var(--ink); letter-spacing: -.02em; }
    .login-card .help { margin-top: 0; color: var(--muted); }

    .shell { display: flex; min-height: 100vh; }
    .side {
      width: 260px; background: #0c1b33;
      color: #fff; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh;
      border-right: 1px solid rgba(255,255,255,.06);
    }
    .side-brand {
      padding: 18px 16px; display: flex; gap: 12px; align-items: center;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .side-brand img { width: 148px; height: 52px; object-fit: contain; object-position: left center; background: #fff; border-radius: 10px; padding: 6px 10px; flex-shrink: 0; }
    .side-brand strong { display: block; font-size: 14px; line-height: 1.25; letter-spacing: -.01em; }
    .side-brand span { display: block; font-size: 11px; color: #94a3b8; margin-top: 2px; text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }

    .side-nav { padding: 12px 10px 20px; overflow: auto; flex: 1; }
    .side-label {
      font-size: 10px; letter-spacing: .1em; text-transform: uppercase;
      color: #64748b; padding: 14px 10px 6px; font-weight: 700;
    }
    .side-nav a {
      display: flex; align-items: center; gap: 10px; color: #cbd5e1; text-decoration: none;
      padding: 10px 12px; border-radius: 8px; margin-bottom: 2px; font-weight: 600; font-size: 14px;
      transition: background .12s ease, color .12s ease;
    }
    .side-nav a:hover { background: rgba(255,255,255,.06); color: #fff; }
    .side-nav a.is-on { background: rgba(11,74,162,.45); color: #fff; }
    .side-nav .ico {
      width: 28px; height: 28px; border-radius: 7px; background: rgba(255,255,255,.06);
      display: inline-flex; align-items: center; justify-content: center; font-size: 12px; color: #93c5fd;
    }
    .side-nav a.is-on .ico { background: rgba(255,255,255,.12); color: #fff; }

    .main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .top {
      background: rgba(255,255,255,.9); backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--line); padding: 12px 24px;
      display: flex; justify-content: space-between; align-items: center; gap: 16px; position: sticky; top: 0; z-index: 20;
    }
    .top-title { font-weight: 700; color: var(--ink); font-size: 14px; letter-spacing: -.01em; }
    .top-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .content { padding: 24px; max-width: 1240px; width: 100%; margin: 0 auto; }

    .flash {
      background: var(--ok-bg); color: var(--ok); padding: 12px 14px; border-radius: 10px;
      margin-bottom: 16px; border: 1px solid #a7f3d0; font-weight: 600; font-size: 14px;
      display: flex; align-items: center; gap: 10px;
    }
    .flash::before { content: "✓"; width: 22px; height: 22px; border-radius: 999px; background: #a7f3d0;
      display: inline-flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
    .panel {
      background: #fff; border: 1px solid var(--line); border-radius: var(--radius);
      padding: 24px; box-shadow: var(--shadow);
    }
    .panel h1 { margin: 0 0 6px; color: var(--ink); font-size: 24px; letter-spacing: -.025em; font-weight: 750; }
    .help { color: var(--muted); font-size: 14px; line-height: 1.55; margin: 0 0 18px; }
    .field-note { color: var(--muted); font-size: 12px; margin: 6px 0 0; }
    label { display: block; font-weight: 700; margin: 16px 0 6px; color: var(--ink); font-size: 13px; }
    input[type=text], input[type=email], input[type=password], input[type=date], input[type=file], input[type=search], select, textarea {
      width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;
    }
    input:focus, select:focus, textarea:focus { outline: 2px solid rgba(11,74,162,.2); border-color: var(--navy); }

    .toolbar {
      display: flex; gap: 12px; align-items: end; justify-content: space-between; flex-wrap: wrap; margin-bottom: 16px;
    }
    .search-box { flex: 1; min-width: 220px; max-width: 420px; }
    .search-box label { margin-top: 0; }

    .actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 20px; align-items: center; }
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 7px;
      background: var(--navy); color: #fff; border: 0; padding: 9px 14px; border-radius: 8px;
      text-decoration: none; cursor: pointer; font-weight: 700; line-height: 1.2; font-size: 13px;
      transition: background .12s ease, border-color .12s ease;
    }
    .btn:hover { background: var(--navy-deep); color: #fff; }
    .btn.gray { background: #475569; }
    .btn.ghost { background: #fff; color: var(--navy); border: 1px solid #cbd5e1; }
    .btn.ghost:hover { background: var(--soft); }
    .btn.red { background: #fff; color: var(--danger); border: 1px solid #fecaca; }
    .btn.green { background: var(--ok); }
    .btn.green:hover { background: #065f46; }
    .btn.warn { background: #fff; color: var(--warn); border: 1px solid #fde68a; }
    .btn.lg { padding: 11px 16px; font-size: 14px; }
    .btn.xl { padding: 14px 20px; font-size: 15px; border-radius: 10px; min-height: 48px; }
    .btn.block { width: 100%; }
    .btn.sm { padding: 8px 12px; font-size: 13px; min-height: 36px; }
    .error { color: var(--danger); margin: 6px 0 0; font-size: 13px; }
    .danger-zone { margin-top: 32px; padding-top: 16px; border-top: 1px solid var(--line); }

    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 750;
      letter-spacing: .02em; text-transform: uppercase;
    }
    .badge-live { background: var(--ok-bg); color: var(--ok); border: 1px solid #a7f3d0; }
    .badge-draft { background: var(--warn-bg); color: var(--warn); border: 1px solid #fde68a; }

    .status-bar {
      display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;
      padding: 12px 14px; border-radius: 10px; border: 1px solid var(--line); background: var(--soft); margin-bottom: 18px;
    }
    .status-bar .meta { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .status-bar .path { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; color: var(--muted); }

    .grid-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 18px 0 24px; }
    .action-card {
      display: block; text-decoration: none; color: inherit; background: #fff;
      border: 1px solid var(--line); border-radius: 12px; padding: 18px; transition: .15s ease;
    }
    .action-card:hover { border-color: #93c5fd; transform: translateY(-1px); box-shadow: var(--shadow); }
    .action-card .eyebrow { color: var(--navy); font-weight: 750; font-size: 11px; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .06em; }
    .eyebrow {
      color: var(--navy); font-weight: 750; font-size: 11px; margin: 0 0 6px;
      text-transform: uppercase; letter-spacing: .06em;
    }
    .action-card h2 { margin: 0 0 6px; font-size: 18px; color: var(--ink); letter-spacing: -.02em; }
    .action-card p { margin: 0; color: var(--muted); font-size: 13px; }
    .stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-bottom: 20px; }
    .stat {
      background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px;
    }
    .stat strong { display: block; font-size: 26px; color: var(--ink); line-height: 1.1; letter-spacing: -.03em; }
    .stat span { color: var(--muted); font-size: 12px; font-weight: 650; }

    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: 11px 10px; border-bottom: 1px solid var(--line); vertical-align: middle; }
    th { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; font-weight: 750; }
    .thumb {
      width: 52px; height: 40px; object-fit: cover; border-radius: 7px; background: #e2e8f0; display: block;
    }
    .teacher-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 6px; background: #e2e8f0; }
    .title-cell { font-weight: 650; color: var(--ink); }
    .muted { color: var(--muted); font-size: 13px; }
    .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .pager { display: flex; gap: 8px; align-items: center; margin-top: 16px; flex-wrap: wrap; }
    .cover-preview { max-width: 280px; border-radius: 10px; border: 1px solid var(--line); margin-top: 8px; }
    .two-col { display: grid; grid-template-columns: 1.4fr .8fr; gap: 16px; }
    .checklist { background: #f8fafc; border: 1px solid var(--line); border-radius: 10px; padding: 14px 16px; }
    .checklist h3 { margin: 0 0 8px; font-size: 14px; color: var(--ink); }
    .checklist ol { margin: 0; padding-left: 18px; color: var(--muted); font-size: 13px; }
    .checklist li { margin: 5px 0; }
    .pages-nav details { border-bottom: 1px solid var(--line); }
    .pages-nav summary {
      cursor: pointer; padding: 10px 4px; font-weight: 750; color: var(--ink); font-size: 13px;
    }
    .pages-nav a {
      display: flex; align-items: center; justify-content: space-between; gap: 8px;
      padding: 7px 8px; border-radius: 7px; text-decoration: none; color: #334155; font-size: 13px;
    }
    .pages-nav a:hover, .pages-nav a.is-on { background: #eff6ff; color: var(--navy); font-weight: 650; }
    .split { display: grid; grid-template-columns: 280px 1fr; gap: 16px; align-items: start; }
    .side-panel { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 12px; max-height: 72vh; overflow: auto; }
    .filter-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
    .filter-tabs a {
      text-decoration: none; padding: 6px 11px; border-radius: 999px; border: 1px solid var(--line);
      color: var(--muted); font-weight: 700; font-size: 12px; background: #fff;
    }
    .filter-tabs a.is-on { background: var(--ink); color: #fff; border-color: var(--ink); }
    .page-table .slug { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; }

    .crumb {
      display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
      font-size: 13px; color: var(--muted); margin: 0 0 14px;
    }
    .crumb a { text-decoration: none; font-weight: 650; }
    .crumb a:hover { text-decoration: underline; }
    .crumb .sep { color: #cbd5e1; }

    .editor-hero {
      display: flex; gap: 16px; align-items: flex-start; justify-content: space-between; flex-wrap: wrap;
      margin-bottom: 16px;
    }
    .editor-hero h1 { margin: 0; }
    .editor-hero .sub { margin: 6px 0 0; color: var(--muted); font-size: 14px; max-width: 52ch; }
    .meta-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
    .meta-chip {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 10px; border-radius: 8px; background: var(--soft);
      border: 1px solid var(--line); font-size: 12px; color: var(--muted); font-weight: 600;
    }
    .meta-chip i { color: #94a3b8; }

    .form-stack { display: flex; flex-direction: column; gap: 14px; }
    .form-section {
      background: #fff; border: 1px solid var(--line); border-radius: var(--radius);
      box-shadow: var(--shadow); overflow: hidden;
    }
    .form-section-head {
      padding: 14px 18px; border-bottom: 1px solid var(--line); background: linear-gradient(180deg, #fff, var(--soft));
      display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;
    }
    .form-section-head .eyebrow {
      color: var(--navy); font-weight: 750; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; margin: 0 0 2px;
    }
    .form-section-head h2 { margin: 0; font-size: 16px; color: var(--ink); letter-spacing: -.015em; }
    .form-section-head p { margin: 3px 0 0; color: var(--muted); font-size: 12px; }
    .form-section-body { padding: 6px 18px 18px; }
    .form-section-body label:first-child { margin-top: 12px; }
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }
    .field-grid .span-2 { grid-column: 1 / -1; }
    .field { margin-top: 4px; }
    .editor-frame {
      margin-top: 8px; border: 1px solid #cbd5e1; border-radius: 10px; overflow: hidden; background: #fff;
    }
    .editor-frame .tox-tinymce { border: 0 !important; border-radius: 0 !important; }
    .sticky-save {
      position: sticky; bottom: 12px; z-index: 15;
      display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap;
      padding: 12px 14px; border-radius: 12px; border: 1px solid var(--line);
      background: rgba(255,255,255,.92); backdrop-filter: blur(10px);
      box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
    }
    .sticky-save .hint { color: var(--muted); font-size: 12px; font-weight: 600; }
    .publish-card {
      background: var(--soft); border: 1px solid var(--line); border-radius: 10px; padding: 14px;
    }
    .publish-card .row { display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .side-rail { display: flex; flex-direction: column; gap: 14px; position: sticky; top: 72px; }
    .rail-card {
      background: #fff; border: 1px solid var(--line); border-radius: var(--radius);
      padding: 14px; box-shadow: var(--shadow);
    }
    .rail-card h3 { margin: 0 0 8px; font-size: 13px; color: var(--ink); letter-spacing: -.01em; }
    .rail-card .hint { margin: 0 0 10px; color: var(--muted); font-size: 12px; line-height: 1.45; }
    .rail-card ol { margin: 0; padding-left: 18px; color: var(--muted); font-size: 12px; }
    .rail-card li { margin: 5px 0; }
    .split-editor { display: grid; grid-template-columns: 1fr 260px; gap: 16px; align-items: start; }
    .danger-zone.soft {
      margin-top: 0; padding: 14px 18px; border-top: 0;
      background: var(--danger-bg); border: 1px solid #fecaca; border-radius: var(--radius);
    }
    .danger-zone.soft h3 { margin: 0 0 6px; font-size: 13px; color: var(--danger); }

    .how-steps {
      display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 0 0 18px;
    }
    .how-step {
      background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px;
      display: flex; gap: 10px; align-items: flex-start; box-shadow: var(--shadow);
    }
    .how-step .n {
      width: 26px; height: 26px; border-radius: 8px; background: #eff6ff; color: var(--navy);
      display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; flex-shrink: 0;
    }
    .how-step strong { display: block; font-size: 13px; color: var(--ink); }
    .how-step span { display: block; font-size: 12px; color: var(--muted); margin-top: 2px; line-height: 1.4; }

    .choice-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .choice {
      position: relative; display: block; cursor: pointer; margin: 0;
      border: 1.5px solid var(--line); border-radius: 10px; padding: 12px; background: #fff;
      transition: border-color .12s ease, background .12s ease, box-shadow .12s ease;
    }
    .choice:hover { border-color: #93c5fd; }
    .choice input { position: absolute; opacity: 0; pointer-events: none; }
    .choice strong { display: block; font-size: 13px; color: var(--ink); }
    .choice span { display: block; font-size: 11px; color: var(--muted); margin-top: 3px; line-height: 1.35; }
    .choice.is-on, .choice:has(input:checked) {
      border-color: var(--navy); background: #eff6ff; box-shadow: inset 0 0 0 1px rgba(11,74,162,.15);
    }
    .choice.live:has(input:checked) { border-color: var(--ok); background: var(--ok-bg); box-shadow: inset 0 0 0 1px rgba(4,120,87,.15); }

    .page-rows { display: flex; flex-direction: column; gap: 8px; }
    .page-row {
      display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center;
      padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; background: #fff;
      transition: border-color .12s ease, box-shadow .12s ease;
    }
    .page-row:hover { border-color: #93c5fd; box-shadow: var(--shadow); }
    .page-row .title-cell { font-size: 15px; }
    .welcome {
      background: linear-gradient(135deg, #0c1b33 0%, #0b4aa2 70%);
      color: #fff; border-radius: 14px; padding: 22px 24px; margin-bottom: 18px;
      display: flex; gap: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap;
    }
    .welcome h1 { margin: 0 0 6px; color: #fff; font-size: 22px; }
    .welcome p { margin: 0; color: #bfdbfe; font-size: 14px; max-width: 52ch; }
    .callout {
      display: flex; gap: 12px; align-items: flex-start;
      padding: 12px 14px; border-radius: 10px; background: #eff6ff; border: 1px solid #bfdbfe; margin-bottom: 16px;
    }
    .callout i { color: var(--navy); margin-top: 2px; }
    .callout strong { display: block; color: var(--ink); font-size: 13px; }
    .callout span { display: block; color: var(--muted); font-size: 12px; margin-top: 2px; line-height: 1.45; }
    .danger-fold > summary {
      cursor: pointer; list-style: none; font-weight: 700; color: var(--danger); font-size: 13px;
      display: flex; align-items: center; gap: 8px;
    }
    .danger-fold > summary::-webkit-details-marker { display: none; }
    .danger-fold[open] { padding-bottom: 4px; }
    .danger-fold .fold-body { margin-top: 12px; }
    .upload-box {
      border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 14px; background: var(--soft); margin-top: 8px;
    }
    .upload-box input[type=file] { margin-top: 6px; }
    .nav-text { display: flex; flex-direction: column; gap: 1px; line-height: 1.2; min-width: 0; }
    .nav-text b { font-weight: 700; font-size: 14px; }
    .nav-text small { font-weight: 500; font-size: 11px; color: #94a3b8; }
    .side-nav a.is-on .nav-text small { color: #bfdbfe; }
    .bn { font-weight: 700; }
    .en-soft { color: var(--muted); font-weight: 500; font-size: 12px; }
    .big-search input {
      font-size: 16px !important; padding: 14px 16px !important; border-radius: 12px !important;
      border: 1.5px solid #cbd5e1 !important;
    }
    .sticky-save { padding: 14px 16px; gap: 12px; }
    .sticky-save .btn.xl { min-width: 160px; }
    .help-fab {
      position: fixed; right: 18px; bottom: 18px; z-index: 40;
      width: 52px; height: 52px; border-radius: 999px; border: 0;
      background: var(--navy); color: #fff; box-shadow: 0 10px 28px rgba(11,74,162,.35);
      cursor: pointer; font-size: 20px;
    }
    .help-panel {
      display: none; position: fixed; right: 18px; bottom: 80px; z-index: 40;
      width: min(340px, calc(100vw - 32px)); background: #fff; border: 1px solid var(--line);
      border-radius: 14px; box-shadow: var(--shadow); padding: 16px;
    }
    .help-panel.is-open { display: block; }
    .help-panel h3 { margin: 0 0 8px; font-size: 15px; color: var(--ink); }
    .help-panel ol { margin: 0; padding-left: 18px; color: var(--muted); font-size: 13px; }
    .help-panel li { margin: 6px 0; }
    .page-row { padding: 16px 18px; gap: 14px; }
    .page-row .row-actions .btn { min-height: 40px; }
    .choice { padding: 14px; min-height: 74px; }
    .choice strong { font-size: 14px; }
    .choice span { font-size: 12px; }
    .teacher-tab-editor { display: flex; flex-direction: column; gap: 10px; }
    .tab-block {
      border: 1px solid var(--line); border-radius: 12px; background: #fff; overflow: hidden;
    }
    .tab-block-featured { border-color: #93c5fd; box-shadow: 0 4px 16px rgba(11,74,162,.08); }
    .tab-block > summary {
      list-style: none; cursor: pointer; display: flex; gap: 12px; align-items: center;
      justify-content: space-between; flex-wrap: wrap; padding: 12px 14px; background: var(--soft);
      border-bottom: 1px solid transparent;
    }
    .tab-block[open] > summary { border-bottom-color: var(--line); }
    .tab-block > summary::-webkit-details-marker { display: none; }
    .tab-block-title { display: flex; flex-direction: column; gap: 2px; }
    .tab-block-title strong { color: var(--ink); font-size: 14px; }
    .tab-block-title small { color: var(--muted); font-size: 11px; font-weight: 650; }
    .tab-block-kicker { color: var(--navy); font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .tab-block-kicker i { margin-right: 4px; }
    .tab-switch {
      display: inline-flex; align-items: center; gap: 8px; margin: 0; font-weight: 700;
      font-size: 12px; color: var(--ink); background: #fff; border: 1px solid var(--line);
      border-radius: 999px; padding: 7px 12px; cursor: pointer;
    }
    .tab-switch input { width: auto; margin: 0; accent-color: var(--ok); }
    .tab-block-body { padding: 8px 14px 16px; min-width: 0; }
    .tab-editor-intro { margin: -2px 0 10px; color: var(--muted); font-size: 12px; line-height: 1.5; }
    .teacher-profile-tabs-body { padding-top: 12px; }
    .teacher-profile-tabs-body .editor-frame { min-width: 0; }
    .tab-block-body label { margin-top: 8px; }
    .section-jump {
      display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
      margin: 0 0 12px; padding: 10px 12px; border-radius: 10px;
      background: var(--soft); border: 1px solid var(--line);
    }
    .section-jump a {
      text-decoration: none; font-size: 12px; font-weight: 700; color: var(--navy);
      padding: 5px 10px; border-radius: 999px; background: #fff; border: 1px solid var(--line);
    }
    .section-jump a:hover { background: #eff6ff; }
    .photo-chooser {
      display: flex; gap: 12px; align-items: center; flex-wrap: wrap;
      margin: 10px 0 12px; padding: 14px 16px; border-radius: 12px;
      border: 1.5px dashed #93c5fd; background: linear-gradient(180deg, #eff6ff, #fff);
    }
    .photo-chooser .btn { min-height: 44px; }
    .photo-chooser .copy strong { display: block; color: var(--ink); font-size: 13px; }
    .photo-chooser .copy span, .photo-chooser [data-photo-status] {
      display: block; color: var(--muted); font-size: 12px; margin-top: 2px;
    }

    .doc-workspace { display: grid; grid-template-columns: 240px 1fr; gap: 16px; align-items: start; }
    .doc-nav {
      position: sticky; top: 72px; background: #fff; border: 1px solid var(--line);
      border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; max-height: calc(100vh - 100px);
      display: flex; flex-direction: column;
    }
    .doc-nav-head {
      padding: 14px 14px 10px; border-bottom: 1px solid var(--line); background: var(--soft);
    }
    .doc-nav-head strong { display: block; font-size: 13px; color: var(--ink); }
    .doc-nav-head span { display: block; font-size: 11px; color: var(--muted); margin-top: 2px; }
    .doc-nav-list { overflow: auto; padding: 8px; }
    .doc-nav-item {
      width: 100%; text-align: left; border: 0; background: transparent; cursor: pointer;
      display: flex; gap: 10px; align-items: flex-start; padding: 10px 10px; border-radius: 8px;
      color: #334155; font: inherit; margin-bottom: 2px;
    }
    .doc-nav-item:hover { background: #f1f5f9; }
    .doc-nav-item.is-on { background: #eff6ff; color: var(--navy); }
    .doc-nav-item .num {
      width: 22px; height: 22px; border-radius: 6px; background: #e2e8f0; color: #475569;
      display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;
    }
    .doc-nav-item.is-on .num { background: var(--navy); color: #fff; }
    .doc-nav-item b { display: block; font-size: 13px; line-height: 1.3; }
    .doc-nav-item small { display: block; font-size: 11px; color: var(--muted); margin-top: 2px; font-weight: 600; }
    .doc-panel {
      background: #fff; border: 1px solid var(--line); border-radius: var(--radius);
      box-shadow: var(--shadow); overflow: hidden; display: none;
    }
    .doc-panel.is-on { display: block; }
    .doc-panel-head {
      position: sticky; top: 56px; z-index: 5;
      padding: 14px 18px; border-bottom: 1px solid var(--line);
      background: rgba(255,255,255,.96); backdrop-filter: blur(8px);
      display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;
    }
    .doc-panel-head .eyebrow { margin-bottom: 2px; }
    .doc-panel-head h2 { margin: 0; font-size: 18px; color: var(--ink); letter-spacing: -.02em; }
    .doc-panel-head p { margin: 3px 0 0; color: var(--muted); font-size: 12px; }
    .doc-panel-body { padding: 16px 18px 20px; }
    .file-select {
      display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center;
      padding: 14px; border: 1.5px solid #bfdbfe; border-radius: 12px; background: #f8fbff; margin: 0 0 14px;
    }
    .file-select label.file-btn {
      margin: 0; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      background: var(--navy); color: #fff; border-radius: 8px; padding: 11px 16px; cursor: pointer;
      font-weight: 700; font-size: 13px; min-height: 44px; white-space: nowrap;
    }
    .file-select label.file-btn:hover { background: var(--navy-deep); }
    .file-select input[type=file] { position: absolute; width: 1px; height: 1px; opacity: 0; overflow: hidden; }
    .file-select .meta strong { display: block; font-size: 13px; color: var(--ink); }
    .file-select .meta span { display: block; font-size: 12px; color: var(--muted); margin-top: 2px; }
    .file-select .preview {
      display: none; margin-top: 10px; grid-column: 1 / -1;
    }
    .file-select .preview.is-on { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .file-select .preview img {
      width: 72px; height: 54px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line);
    }
    .layout-demo { display: grid; gap: 6px; width: 180px; flex-shrink: 0; }
    .layout-demo .pair { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .layout-demo i {
      display: block; height: 36px; border-radius: 4px; background: #dbeafe;
      border: 1px dashed #93c5fd;
    }
    .layout-demo .wide { height: 42px; }
    .photo-board { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .photo-slot {
      border: 1px solid var(--line); border-radius: 12px; background: #fff; padding: 12px;
    }
    .photo-slot-top { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
    .photo-slot-top .num {
      width: 24px; height: 24px; border-radius: 7px; background: var(--navy); color: #fff;
      display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;
    }
    .photo-slot-top strong { font-size: 13px; color: var(--ink); }
    .photo-slot-top span { margin-left: auto; font-size: 11px; color: var(--muted); font-weight: 700; }
    .photo-slot img.slot-preview {
      width: 100%; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); margin-bottom: 8px;
    }
    .photo-slot .slot-actions { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
    .photo-tools { display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-top: 12px; }

    .where-box {
      display: grid; grid-template-columns: 1.2fr .8fr; gap: 12px; margin-bottom: 16px;
    }
    .where-card, .guide-card {
      background: #fff; border: 1px solid var(--line); border-radius: var(--radius);
      padding: 14px 16px; box-shadow: var(--shadow);
    }
    .where-card h3, .guide-card h3 {
      margin: 0 0 8px; font-size: 13px; color: var(--ink); letter-spacing: -.01em;
    }
    .site-path {
      display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
      font-size: 13px; font-weight: 650; color: var(--ink);
    }
    .site-path .seg {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 6px 10px; border-radius: 8px; background: var(--soft); border: 1px solid var(--line);
    }
    .site-path .seg.is-active { background: #eff6ff; border-color: #93c5fd; color: var(--navy); }
    .site-path .arrow { color: #94a3b8; font-size: 11px; }
    .where-url {
      margin-top: 10px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; color: var(--muted);
    }
    .where-url a { font-weight: 650; text-decoration: none; }
    .guide-list { margin: 0; padding: 0; list-style: none; }
    .guide-list li {
      display: grid; grid-template-columns: 22px 1fr; gap: 8px; align-items: start;
      padding: 7px 0; border-top: 1px solid var(--line); font-size: 12px; color: var(--muted);
    }
    .guide-list li:first-child { border-top: 0; padding-top: 0; }
    .guide-list .n {
      width: 22px; height: 22px; border-radius: 6px; background: #eff6ff; color: var(--navy);
      display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px;
    }
    .guide-list strong { color: var(--ink); }

    .now-editing {
      display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap;
      padding: 12px 14px; border-radius: 12px; border: 1px solid #bfdbfe; background: #f8fbff;
      margin-bottom: 14px;
    }
    .now-editing .label { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--navy); font-weight: 750; margin: 0 0 2px; }
    .now-editing .value { margin: 0; font-size: 15px; font-weight: 750; color: var(--ink); letter-spacing: -.015em; }
    .now-editing .hint { margin: 2px 0 0; font-size: 12px; color: var(--muted); }

    .page-group { margin-bottom: 18px; }
    .page-group-head {
      display: flex; align-items: baseline; justify-content: space-between; gap: 10px; flex-wrap: wrap;
      margin: 0 0 8px; padding: 0 2px;
    }
    .page-group-head h2 { margin: 0; font-size: 14px; color: var(--ink); letter-spacing: -.01em; }
    .page-group-head span { font-size: 12px; color: var(--muted); font-weight: 600; }
    .page-row .where-line {
      margin-top: 6px; font-size: 12px; color: var(--muted); display: flex; gap: 6px; align-items: center; flex-wrap: wrap;
    }
    .page-row .where-line code {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      background: var(--soft); border: 1px solid var(--line); border-radius: 6px; padding: 2px 6px; font-size: 11px;
    }

    .map-card {
      background: linear-gradient(180deg, #fff, #f8fafc); border: 1px solid var(--line);
      border-radius: 12px; padding: 14px 16px; margin-bottom: 16px;
    }
    .map-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .map-item {
      background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 12px;
      text-decoration: none; color: inherit; transition: border-color .12s ease, box-shadow .12s ease;
    }
    .map-item:hover { border-color: #93c5fd; box-shadow: var(--shadow); }
    .map-item strong { display: block; font-size: 13px; color: var(--ink); }
    .map-item span { display: block; font-size: 12px; color: var(--muted); margin-top: 3px; line-height: 1.4; }

    .content.has-dock { padding-bottom: 96px; }
    .save-dock .editing-chip {
      display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; border-radius: 999px;
      background: #eff6ff; color: var(--navy); font-size: 11px; font-weight: 750;
    }

    @media (max-width: 980px) {
      .shell { display: block; }
      .side { width: 100%; height: auto; position: relative; }
      .side-nav { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); max-height: none; padding-bottom: 12px; }
      .side-label { grid-column: 1 / -1; }
      .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .grid-cards, .two-col, .split, .split-editor, .field-grid, .how-steps, .choice-grid { grid-template-columns: 1fr; }
      .side-rail { position: static; }
      .page-row { grid-template-columns: 1fr; }
      .doc-workspace { grid-template-columns: 1fr; }
      .doc-nav { position: static; max-height: none; }
      .doc-panel-head { position: static; }
      .file-select { grid-template-columns: 1fr; }
      .where-box, .map-grid, .photo-board { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
@if(session('is_admin'))
  @php $nav = $nav ?? ''; @endphp
  <div class="shell">
    <aside class="side">
      <div class="side-brand">
        <img src="{{ asset('wp-content/uploads/2023/07/black-uu-logo.png') }}" alt="Uttara University logo">
        <div>
          <strong>UU Admin Portal</strong>
          <span>Content Management Portal</span>
        </div>
      </div>
      <nav class="side-nav">
        <div class="side-label">Overview</div>
        <a class="{{ $nav === 'dashboard' ? 'is-on' : '' }}" href="{{ route('admin.dashboard') }}">
          <span class="ico"><i class="fa-solid fa-house"></i></span>
          <span class="nav-text"><b>Dashboard</b><small>What to change where</small></span>
        </a>

        <div class="side-label">Publish</div>
        <a class="{{ $nav === 'news' ? 'is-on' : '' }}" href="{{ route('admin.news.index') }}">
          <span class="ico"><i class="fa-regular fa-newspaper"></i></span>
          <span class="nav-text"><b>News &amp; Events</b><small>Stories on website</small></span>
        </a>
        <a class="{{ $nav === 'notices' ? 'is-on' : '' }}" href="{{ route('admin.notices.index') }}">
          <span class="ico"><i class="fa-solid fa-bullhorn"></i></span>
          <span class="nav-text"><b>Notices</b><small>Announcements</small></span>
        </a>
        <a class="{{ $nav === 'pages' ? 'is-on' : '' }}" href="{{ route('admin.pages.index') }}" style="justify-content:flex-start;">
          <span class="ico"><i class="fa-regular fa-file-lines"></i></span>
          <span class="nav-text" style="flex:1;"><b>Website pages</b><small>Edit text &amp; photos</small></span>
          @if(($pendingApprovalCount ?? 0) > 0)
            <span class="badge badge-draft" style="margin-left:auto;">{{ $pendingApprovalCount }}</span>
          @endif
        </a>

        <div class="side-label">Homepage</div>
        <a class="{{ $nav === 'home' ? 'is-on' : '' }}" href="{{ route('admin.home.edit') }}">
          <span class="ico"><i class="fa-regular fa-images"></i></span>
          <span class="nav-text"><b>Banners</b><small>Slider &amp; ticker</small></span>
        </a>
        <a class="{{ $nav === 'rankings' ? 'is-on' : '' }}" href="{{ route('admin.rankings.index') }}">
          <span class="ico"><i class="fa-solid fa-trophy"></i></span>
          <span class="nav-text"><b>Rankings</b><small>Awards</small></span>
        </a>

        <div class="side-label">Media</div>
        <a class="{{ $nav === 'videos' ? 'is-on' : '' }}" href="{{ route('admin.videos.index') }}">
          <span class="ico"><i class="fa-brands fa-youtube"></i></span>
          <span class="nav-text"><b>Videos</b><small>YouTube</small></span>
        </a>
        <a class="{{ $nav === 'albums' ? 'is-on' : '' }}" href="{{ route('admin.albums.index') }}">
          <span class="ico"><i class="fa-regular fa-image"></i></span>
          <span class="nav-text"><b>Albums</b><small>Gallery</small></span>
        </a>

        <div class="side-label">Structure</div>
        <a class="{{ $nav === 'teachers' ? 'is-on' : '' }}" href="{{ route('admin.teachers.index') }}">
          <span class="ico"><i class="fa-solid fa-chalkboard-user"></i></span>
          <span class="nav-text"><b>Teachers</b><small>Faculty &amp; staff</small></span>
        </a>
        <a class="{{ $nav === 'menus' ? 'is-on' : '' }}" href="{{ route('admin.menus.edit') }}">
          <span class="ico"><i class="fa-solid fa-bars"></i></span>
          <span class="nav-text"><b>Menus</b><small>Navigation</small></span>
        </a>
        <a class="{{ $nav === 'settings' ? 'is-on' : '' }}" href="{{ route('admin.settings.edit') }}">
          <span class="ico"><i class="fa-solid fa-gear"></i></span>
          <span class="nav-text"><b>Settings</b><small>Site-wide</small></span>
        </a>
      </nav>
    </aside>
    <div class="main">
      <div class="top">
        <div class="top-title">@yield('top', 'Website content')</div>
        <div class="top-actions">
          <a class="btn ghost sm" href="/" target="_blank" rel="noopener"><i class="fa-solid fa-eye"></i> View website</a>
          <form method="post" action="{{ route('admin.logout') }}" style="margin:0;">
            @csrf
            <button class="btn gray sm" type="submit">Sign out</button>
          </form>
        </div>
      </div>
      <div class="content">
        @if(session('status'))
          <div class="flash">{{ session('status') }}</div>
        @endif
        @yield('content')
      </div>
    </div>
  </div>
  <div class="help-panel" id="helpPanel">
    <h3>How to update the website</h3>
    <ol>
      <li>Left menu → open the area you want (News, Notices, or Website pages)</li>
      <li>On a page, pick the tab/section that matches the website</li>
      <li>Change text or Select photo from your computer</li>
      <li>Click <strong>Save changes</strong> — if the page is Approved, visitors see it</li>
      <li>Use <strong>Make live</strong> if a page is still Draft</li>
    </ol>
  </div>
  <button type="button" class="help-fab" id="helpFab" title="Help" aria-label="Help">?</button>
  <script>
    (function () {
      const fab = document.getElementById('helpFab');
      const panel = document.getElementById('helpPanel');
      fab?.addEventListener('click', () => panel?.classList.toggle('is-open'));
      document.addEventListener('click', (e) => {
        if (!panel || !fab) return;
        if (!panel.contains(e.target) && !fab.contains(e.target)) panel.classList.remove('is-open');
      });
    })();
  </script>
@else
  @yield('content')
@endif
</body>
</html>
