<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Humis - Žmogiškųjų resursų paskirstymo sistema')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #f8f9fa;
            --bg-white: #ffffff;
            --bg-sidebar: #1e2532;
            --bg-sidebar-hover: #2a3444;
            --bg-sidebar-active: #343f52;
            
            --text-dark: #1a1a2e;
            --text-primary: #374151;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --text-sidebar: #a0aec0;
            --text-sidebar-active: #ffffff;
            
            --border-color: #e5e7eb;
            --border-light: #f3f4f6;
            
            --accent: #10b981;
            --accent-hover: #059669;
            --accent-light: #d1fae5;
            --accent-bg: #ecfdf5;
            
            --info: #3b82f6;
            --info-bg: #eff6ff;
            --warning: #f59e0b;
            --warning-bg: #fffbeb;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            
            --radius: 8px;
            --radius-lg: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: var(--bg-sidebar);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar__header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar__logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .sidebar__logo-text {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .sidebar__logo-text span {
            color: var(--accent);
        }

        .sidebar__nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .sidebar__section {
            margin-bottom: 24px;
        }

        .sidebar__section-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .sidebar__menu {
            list-style: none;
        }

        .sidebar__link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: var(--text-sidebar);
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s ease;
            margin-bottom: 2px;
        }

        .sidebar__link:hover {
            background: var(--bg-sidebar-hover);
            color: var(--text-sidebar-active);
        }

        .sidebar__link--active {
            background: var(--bg-sidebar-active);
            color: var(--text-sidebar-active);
        }

        .sidebar__link--active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
        }

        .sidebar__icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
        }

        .sidebar__badge {
            margin-left: auto;
            background: var(--accent);
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .sidebar__footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar__user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar__avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .sidebar__user-info {
            flex: 1;
        }

        .sidebar__user-name {
            color: white;
            font-weight: 600;
            font-size: 13px;
        }

        .sidebar__user-role {
            color: var(--text-sidebar);
            font-size: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        .sidebar__logout {
            background: transparent;
            border: none;
            color: var(--text-sidebar);
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .sidebar__logout:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .main {
            flex: 1;
            margin-left: 260px;
            min-height: 100vh;
        }

        .main__header {
            background: var(--bg-white);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 32px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .main__header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .main__title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .main__subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .main__content {
            padding: 32px;
        }

        .card {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .card__header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card__title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .card__body {
            padding: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn--primary {
            background: var(--accent);
            color: white;
        }

        .btn--primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn--secondary {
            background: var(--bg-white);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn--secondary:hover {
            background: var(--bg-body);
            border-color: var(--text-muted);
        }

        .btn--danger {
            background: var(--danger);
            color: white;
        }

        .btn--sm {
            padding: 6px 14px;
            font-size: 13px;
        }

        .btn--icon {
            padding: 8px;
            width: 36px;
            height: 36px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 500;
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: var(--bg-body);
            border-bottom: 1px solid var(--border-color);
        }

        .table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--bg-body);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 6px;
            color: var(--text-dark);
        }

        .form-input {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background: var(--bg-white);
            transition: all 0.15s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
        }

        .badge--success {
            background: var(--accent-bg);
            color: var(--accent-hover);
        }

        .badge--warning {
            background: var(--warning-bg);
            color: #b45309;
        }

        .badge--danger {
            background: var(--danger-bg);
            color: #b91c1c;
        }

        .badge--info {
            background: var(--info-bg);
            color: #1d4ed8;
        }

        .badge--neutral {
            background: var(--bg-body);
            color: var(--text-secondary);
        }

        .grid {
            display: grid;
            gap: 24px;
        }

        .grid--2 { grid-template-columns: repeat(2, 1fr); }
        .grid--3 { grid-template-columns: repeat(3, 1fr); }
        .grid--4 { grid-template-columns: repeat(4, 1fr);         }

        .stat-card {
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 24px;
        }

        .stat-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .stat-card__icon--green { background: var(--accent-bg); }
        .stat-card__icon--blue { background: var(--info-bg); }
        .stat-card__icon--yellow { background: var(--warning-bg); }
        .stat-card__icon--red { background: var(--danger-bg); }

        .stat-card__value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-card__label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
        }

        .avatar--sm { width: 32px; height: 32px; font-size: 12px; }
        .avatar--lg { width: 56px; height: 56px; font-size: 20px;         }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state__icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: var(--bg-body);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
        }

        .empty-state__title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .empty-state__text {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .alert--success {
            background: var(--accent-bg);
            color: var(--accent-hover);
            border: 1px solid var(--accent-light);
        }

        .alert--warning {
            background: var(--warning-bg);
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .alert--danger {
            background: var(--danger-bg);
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .alert--info {
            background: var(--info-bg);
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .code-block {
            background: #1e2532;
            color: #e5e7eb;
            padding: 16px 20px;
            border-radius: var(--radius);
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 13px;
            overflow-x: auto;
        }

        .code-block .comment { color: #6b7280; }
        .code-block .string { color: #10b981;         }

        .user-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-row__info {
            flex: 1;
            min-width: 0;
        }

        .user-row__name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .user-row__email {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .skill-dots {
            display: flex;
            gap: 4px;
        }

        .skill-dots__dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--border-color);
        }

        .skill-dots__dot--filled {
            background: var(--accent);
        }

        .quick-link {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            background: var(--bg-body);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .quick-link:hover {
            background: var(--bg-white);
            border-color: var(--border-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .quick-link__icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .quick-link__title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .quick-link__desc {
            font-size: 13px;
            color: var(--text-secondary);
        }

        @media (max-width: 1024px) {
            .sidebar { width: 220px; }
            .main { margin-left: 220px; }
            .grid--4 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; }
            .grid--2, .grid--3 { grid-template-columns: 1fr; }
        }

        .searchable-select {
            position: relative;
            width: 100%;
        }

        .searchable-select__trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            text-align: left;
            cursor: pointer;
            background: var(--bg-white);
        }

        .searchable-select__trigger:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .searchable-select__value {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .searchable-select__placeholder {
            color: var(--text-muted);
        }

        .searchable-select__arrow {
            flex-shrink: 0;
            color: var(--text-muted);
            transition: transform 0.2s;
        }

        .searchable-select.is-open .searchable-select__arrow {
            transform: rotate(180deg);
        }

        .searchable-select__avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
        }

        .searchable-select__dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 4px;
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            z-index: 1000;
            display: none;
            max-height: 320px;
            overflow: hidden;
        }

        .searchable-select.is-open .searchable-select__dropdown {
            display: block;
        }

        .searchable-select__search-wrapper {
            position: relative;
            padding: 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .searchable-select__search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .searchable-select__search {
            width: 100%;
            padding: 8px 12px 8px 36px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-body);
            transition: all 0.15s ease;
        }

        .searchable-select__search:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--bg-white);
        }

        .searchable-select__options {
            list-style: none;
            margin: 0;
            padding: 4px;
            max-height: 240px;
            overflow-y: auto;
        }

        .searchable-select__option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.1s;
        }

        .searchable-select__option:hover {
            background: var(--bg-body);
        }

        .searchable-select__option--selected {
            background: var(--accent-bg);
            color: var(--accent-hover);
            font-weight: 500;
        }

        .searchable-select__option--selected:hover {
            background: var(--accent-light);
        }

        .searchable-select__option--hidden {
            display: none;
        }

        .searchable-select__option-label {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .searchable-select__empty {
            padding: 16px;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar__header">
                <a href="{{ route('dashboard') }}" class="sidebar__logo">
                    <span class="sidebar__logo-text">Humis</span>
                </a>
            </div>

            <nav class="sidebar__nav">
                <div class="sidebar__section">
                    <ul class="sidebar__menu">
                        <li>
                            <a href="{{ route('dashboard') }}" class="sidebar__link {{ request()->routeIs('dashboard') ? 'sidebar__link--active' : '' }}">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                </span>
                                <span>Pradžia</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar__section">
                    <div class="sidebar__section-title">Valdymas</div>
                    <ul class="sidebar__menu">
                        <li>
                            <a href="{{ route('vacations.index') }}" class="sidebar__link {{ request()->routeIs('vacations.*') ? 'sidebar__link--active' : '' }}">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                                <span>Atostogos</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('employees.index') }}" class="sidebar__link {{ request()->routeIs('employees.*') ? 'sidebar__link--active' : '' }}">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </span>
                                <span>Darbuotojai</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('projects.index') }}" class="sidebar__link {{ request()->routeIs('projects.*') ? 'sidebar__link--active' : '' }}">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                </span>
                                <span>Projektai</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('skills.index') }}" class="sidebar__link {{ request()->routeIs('skills.*') ? 'sidebar__link--active' : '' }}">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </span>
                                <span>Įgūdžiai</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="sidebar__section">
                    <div class="sidebar__section-title">Sistema</div>
                    <ul class="sidebar__menu">
                        <li>
                            <a href="{{ route('settings.index') }}" class="sidebar__link {{ request()->routeIs('settings.*') ? 'sidebar__link--active' : '' }}">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                </span>
                                <span>Nustatymai</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('sync.index') }}" class="sidebar__link {{ request()->routeIs('sync.*') ? 'sidebar__link--active' : '' }}">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                </span>
                                <span>Sinchronizacija</span>
                            </a>
                        </li>
                        <li>
                            <a href="https://app.clickup.com" target="_blank" class="sidebar__link">
                                <span class="sidebar__icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </span>
                                <span>ClickUp</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="sidebar__footer">
                @auth
                <div class="sidebar__user">
                    <div class="sidebar__avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                    <div class="sidebar__user-info">
                        <div class="sidebar__user-name">{{ Auth::user()->name }}</div>
                        <div class="sidebar__user-role">{{ Auth::user()->email }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: 8px;">
                        @csrf
                        <button type="submit" class="sidebar__logout" title="Atsijungti">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </aside>

        <main class="main">
            <header class="main__header">
                <div class="main__header-content">
                    <div>
                        <h1 class="main__title">@yield('page-title', 'Pradžia')</h1>
                        @hasSection('page-subtitle')
                            <p class="main__subtitle">@yield('page-subtitle')</p>
                        @endif
                    </div>
                    @yield('header-actions')
                </div>
            </header>

            <div class="main__content">
                @if(session('success'))
                    <div class="alert alert--success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert--danger">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initSearchableSelects();
    });

    function initSearchableSelects() {
        document.querySelectorAll('.searchable-select').forEach(function(container) {
            if (container.dataset.initialized) return;
            container.dataset.initialized = 'true';

            const trigger = container.querySelector('.searchable-select__trigger');
            const dropdown = container.querySelector('.searchable-select__dropdown');
            const searchInput = container.querySelector('.searchable-select__search');
            const options = container.querySelectorAll('.searchable-select__option');
            const hiddenInput = container.querySelector('input[type="hidden"]');
            const valueDisplay = container.querySelector('.searchable-select__value');
            const emptyState = container.querySelector('.searchable-select__empty');
            const showAvatar = container.querySelector('.searchable-select__avatar') !== null;

            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                const isOpen = container.classList.contains('is-open');

                document.querySelectorAll('.searchable-select.is-open').forEach(function(other) {
                    if (other !== container) {
                        other.classList.remove('is-open');
                    }
                });

                container.classList.toggle('is-open');
                
                if (!isOpen) {
                    searchInput.value = '';
                    filterOptions('');
                    setTimeout(() => searchInput.focus(), 50);
                }
            });

            searchInput.addEventListener('input', function() {
                filterOptions(this.value.toLowerCase());
            });

            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            options.forEach(function(option) {
                option.addEventListener('click', function() {
                    const value = this.dataset.value;
                    const label = this.dataset.label || this.textContent.trim();
                    const color = this.dataset.color || '#6366f1';

                    hiddenInput.value = value;

                    let html = '';
                    if (showAvatar) {
                        html += `<span class="searchable-select__avatar" style="background: ${color}">${label.charAt(0)}</span>`;
                    }
                    html += label;
                    valueDisplay.innerHTML = html;

                    options.forEach(opt => opt.classList.remove('searchable-select__option--selected'));
                    this.classList.add('searchable-select__option--selected');

                    container.classList.remove('is-open');

                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });

            function filterOptions(query) {
                let visibleCount = 0;
                
                options.forEach(function(option) {
                    const label = (option.dataset.label || option.textContent).toLowerCase();

                    if (label.includes(query)) {
                        option.classList.remove('searchable-select__option--hidden');
                        visibleCount++;
                    } else {
                        option.classList.add('searchable-select__option--hidden');
                    }
                });

                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 && query.length > 0 ? 'block' : 'none';
                }
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.searchable-select')) {
                document.querySelectorAll('.searchable-select.is-open').forEach(function(container) {
                    container.classList.remove('is-open');
                });
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.searchable-select.is-open').forEach(function(container) {
                    container.classList.remove('is-open');
                });
            }
        });
    }
    </script>

    @stack('scripts')
</body>
</html>
