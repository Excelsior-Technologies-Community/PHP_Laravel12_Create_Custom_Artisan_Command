<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Laravel Artisan Command Runner</title>

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(135deg,
                    #667eea 0%,
                    #764ba2 100%);
            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 22px;
            padding: 28px;
            margin-bottom: 25px;
            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .page-header h1 {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #1f2937;
        }

        .page-header p {
            color: #6b7280;
            margin-bottom: 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-history {
            background: #111827;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-history:hover {
            background: #000;
            color: white;
        }

        .stats-row {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 18px;
            padding: 20px;
            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-title {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 7px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #111827;
        }

        .toolbar {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .toolbar-title {
            font-size: 17px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 15px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding-left: 42px;
            border-radius: 11px;
            height: 46px;
            border: 1px solid #d1d5db;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 11px;
            color: #9ca3af;
            font-size: 18px;
        }

        .filter-select {
            height: 46px;
            border-radius: 11px;
            border: 1px solid #d1d5db;
        }

        .command-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.13);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .command-card:hover {
            transform: translateY(-3px);
            box-shadow:
                0 20px 45px rgba(0, 0, 0, 0.17);
        }

        .command-card.hidden {
            display: none;
        }

        .command-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .command-name {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            word-break: break-word;
        }

        .command-description {
            color: #6b7280;
            font-size: 14px;
            margin-top: 5px;
        }

        .badge-custom {
            background: #ede9fe;
            color: #6d28d9;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-built-in {
            background: #e0f2fe;
            color: #0369a1;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .signature {
            background: #111827;
            color: #e5e7eb;
            padding: 12px 15px;
            border-radius: 10px;
            font-family: Consolas, monospace;
            font-size: 13px;
            margin-bottom: 18px;
            overflow-x: auto;
        }

        .field-label {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
        }

        .argument-input,
        .option-input {
            height: 44px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            width: 100%;
            padding: 0 13px;
        }

        .argument-input:focus,
        .option-input:focus {
            border-color: #667eea;
            box-shadow:
                0 0 0 3px rgba(102, 126, 234, 0.12);
            outline: none;
        }

        .help-text {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 5px;
        }

        .command-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            gap: 10px;
        }

        .favorite-btn {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background: white;
            font-size: 20px;
            cursor: pointer;
            transition: 0.2s;
        }

        .favorite-btn:hover {
            transform: scale(1.05);
        }

        .favorite-btn.active {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .run-btn {
            background:
                linear-gradient(135deg,
                    #667eea,
                    #764ba2);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 10px 22px;
            font-weight: 700;
            min-width: 120px;
        }

        .run-btn:hover {
            opacity: 0.92;
            color: white;
        }

        .run-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .output-box {
            display: none;
            margin-top: 20px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .output-box.show {
            display: block;
        }

        .output-header {
            padding: 12px 15px;
            background: #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .output-title {
            font-size: 13px;
            font-weight: 800;
            color: #374151;
        }

        .status-success {
            color: #15803d;
            font-weight: 800;
        }

        .status-failed {
            color: #dc2626;
            font-weight: 800;
        }

        .output-content {
            margin: 0;
            padding: 15px;
            background: #111827;
            color: #d1d5db;
            font-family: Consolas, monospace;
            font-size: 13px;
            min-height: 70px;
            max-height: 350px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .empty-state {
            display: none;
            text-align: center;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 18px;
            padding: 50px 20px;
            color: #6b7280;
        }

        .empty-state.show {
            display: block;
        }

        .loading-spinner {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 7px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .toast-container-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .custom-toast {
            min-width: 300px;
            background: white;
            border-radius: 12px;
            padding: 15px 18px;
            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.18);
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }

        .custom-toast.success {
            border-left-color: #16a34a;
        }

        .custom-toast.error {
            border-left-color: #dc2626;
        }

        @media (max-width: 992px) {

            .stats-row {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 768px) {

            .main-container {
                padding: 20px 12px;
            }

            .page-header {
                padding: 20px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .command-top {
                flex-direction: column;
            }

            .command-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .run-btn {
                width: 100%;
            }

        }

        @media (max-width: 576px) {

            .stats-row {
                grid-template-columns: 1fr;
            }

            .header-actions {
                width: 100%;
            }

            .btn-history {
                width: 100%;
                text-align: center;
            }

        }
    </style>

</head>

<body>

    <div class="main-container">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}

        <div class="page-header">

            <div
                class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">

                <div>

                    <h1>
                        ⚡ Laravel Artisan Command Runner
                    </h1>

                    <p>
                        Run and monitor your Laravel Artisan commands from one place.
                    </p>

                </div>

                <div class="header-actions">

                    <a
                        href="/command-history"
                        class="btn-history">
                        📜 Command History
                    </a>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- STATISTICS --}}
        {{-- ========================================================= --}}

        <div class="stats-row">

            <div class="stat-card">

                <div class="stat-title">
                    Total Commands
                </div>

                <div
                    class="stat-value"
                    id="totalCommands">
                    {{ $commands->count() }}
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Custom Commands
                </div>

                <div
                    class="stat-value"
                    id="customCommands">
                    0
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Built-in Commands
                </div>

                <div
                    class="stat-value"
                    id="builtinCommands">
                    0
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-title">
                    Visible Commands
                </div>

                <div
                    class="stat-value"
                    id="visibleCommands">
                    {{ $commands->count() }}
                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- SEARCH + FILTER --}}
        {{-- ========================================================= --}}

        <div class="toolbar">

            <div class="toolbar-title">
                🔎 Find Artisan Commands
            </div>

            <div class="row g-3">

                <div class="col-lg-7">

                    <div class="search-box">

                        <span class="search-icon">
                            🔍
                        </span>

                        <input
                            type="text"
                            id="commandSearch"
                            class="form-control"
                            placeholder="Search command name or description...">

                    </div>

                </div>


                <div class="col-lg-3">

                    <select
                        id="commandFilter"
                        class="form-select filter-select">

                        <option value="all">
                            All Commands
                        </option>

                        <option value="custom">
                            Custom Commands
                        </option>

                        <option value="builtin">
                            Built-in Commands
                        </option>

                        <option value="favorite">
                            ⭐ Favorites
                        </option>

                    </select>

                </div>


                <div class="col-lg-2">

                    <button
                        type="button"
                        class="btn btn-dark w-100"
                        style="height:46px;border-radius:11px;"
                        onclick="resetFilters()">
                        Reset
                    </button>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- COMMAND LIST --}}
        {{-- ========================================================= --}}

        <div id="commandsContainer">

            @forelse($commands as $command)

            @php

            $isCustom =
            str_starts_with($command['name'], 'create:')
            ||
            str_starts_with($command['name'], 'delete:')
            ||
            str_starts_with($command['name'], 'list:')
            ||
            str_starts_with($command['name'], 'user:')
            ||
            str_starts_with($command['name'], 'db:')
            ||
            str_starts_with($command['name'], 'cache:')
            ||
            str_starts_with($command['name'], 'schedule:')
            ||
            str_starts_with($command['name'], 'app:')
            ||
            str_starts_with($command['name'], 'command:');

            @endphp


            <div
                class="command-card"
                data-command="{{ strtolower($command['name']) }}"
                data-description="{{ strtolower($command['description'] ?? '') }}"
                data-type="{{ $isCustom ? 'custom' : 'builtin' }}"
                data-favorite="false">

                {{-- ================================================= --}}
                {{-- COMMAND HEADER --}}
                {{-- ================================================= --}}

                <div class="command-top">

                    <div>

                        <div class="command-name">

                            {{ $command['name'] }}

                        </div>

                        <div class="command-description">

                            {{ $command['description'] ?: 'No description available.' }}

                        </div>

                    </div>


                    <div>

                        @if($isCustom)

                        <span class="badge-custom">
                            CUSTOM
                        </span>

                        @else

                        <span class="badge-built-in">
                            BUILT-IN
                        </span>

                        @endif

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- SIGNATURE --}}
                {{-- ================================================= --}}

                <div class="signature">

                    {{ $command['signature'] }}

                </div>


                {{-- ================================================= --}}
                {{-- INPUTS --}}
                {{-- ================================================= --}}

                <div class="row g-3">

                    {{-- Arguments --}}

                    <div class="col-lg-6">

                        <label class="field-label">
                            Arguments
                        </label>

                        <input
                            type="text"
                            class="argument-input"
                            data-role="arguments"
                            placeholder="Example: 10">

                        <div class="help-text">

                            Enter arguments separated by spaces.

                        </div>

                    </div>


                    {{-- Options --}}

                    <div class="col-lg-6">

                        <label class="field-label">
                            Options
                        </label>

                        <input
                            type="text"
                            class="option-input"
                            data-role="options"
                            placeholder="Example: --verified, --limit=20">

                        <div class="help-text">

                            Separate options using commas.

                        </div>

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- ACTIONS --}}
                {{-- ================================================= --}}

                <div class="command-actions">

                    <button
                        type="button"
                        class="favorite-btn"
                        data-role="favorite"
                        title="Add to favorites"
                        onclick="toggleFavorite(this)">
                        ☆
                    </button>


                    <button
                        type="button"
                        class="run-btn"
                        onclick="runCommand(this)">
                        ▶ Run Command
                    </button>

                </div>


                {{-- ================================================= --}}
                {{-- OUTPUT --}}
                {{-- ================================================= --}}

                <div
                    class="output-box"
                    data-role="output-box">

                    <div class="output-header">

                        <div class="output-title">

                            Command Result

                        </div>

                        <div
                            data-role="status">
                        </div>

                    </div>


                    <div class="row g-0">

                        <div class="col-md-4">

                            <div
                                class="p-3 border-end">

                                <small class="text-muted d-block">
                                    Exit Code
                                </small>

                                <strong
                                    data-role="exit-code">
                                    -
                                </strong>

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div
                                class="p-3 border-end">

                                <small class="text-muted d-block">
                                    Duration
                                </small>

                                <strong
                                    data-role="duration">
                                    -
                                </strong>

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="p-3">

                                <small class="text-muted d-block">
                                    Status
                                </small>

                                <strong
                                    data-role="status-text">
                                    -
                                </strong>

                            </div>

                        </div>

                    </div>


                    <pre
                        class="output-content"
                        data-role="output"></pre>

                </div>

            </div>

            @empty

            <div class="empty-state show">

                <h4>
                    No Artisan commands found.
                </h4>

                <p>
                    Run <code>php artisan list</code> to check your commands.
                </p>

            </div>

            @endforelse

        </div>


        {{-- ========================================================= --}}
        {{-- SEARCH EMPTY STATE --}}
        {{-- ========================================================= --}}

        <div
            id="searchEmptyState"
            class="empty-state">

            <h4>
                🔍 No Commands Found
            </h4>

            <p>
                Try another search keyword or change the filter.
            </p>

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- TOAST --}}
    {{-- ============================================================= --}}

    <div
        id="toastContainer"
        class="toast-container-custom">
    </div>


    <script>
        /*
    |--------------------------------------------------------------------------
    | CSRF TOKEN
    |--------------------------------------------------------------------------
    */

        const csrfToken =
            document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content');


        /*
        |--------------------------------------------------------------------------
        | DOM ELEMENTS
        |--------------------------------------------------------------------------
        */

        const searchInput =
            document.getElementById('commandSearch');

        const filterInput =
            document.getElementById('commandFilter');

        const commandCards =
            document.querySelectorAll('.command-card');


        /*
        |--------------------------------------------------------------------------
        | Local Storage Favorites
        |--------------------------------------------------------------------------
        */

        const FAVORITES_KEY =
            'artisan_command_favorites';


        function getFavorites() {
            try {

                return JSON.parse(
                    localStorage.getItem(FAVORITES_KEY) || '[]'
                );

            } catch (error) {

                return [];
            }
        }


        function saveFavorites(favorites) {
            localStorage.setItem(
                FAVORITES_KEY,
                JSON.stringify(favorites)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Initialize Favorites
        |--------------------------------------------------------------------------
        */

        function initializeFavorites() {
            const favorites = getFavorites();

            commandCards.forEach(card => {

                const commandName =
                    card.dataset.command;

                const button =
                    card.querySelector(
                        '[data-role="favorite"]'
                    );

                if (
                    favorites.includes(commandName)
                ) {

                    card.dataset.favorite = 'true';

                    button.classList.add('active');

                    button.textContent = '★';

                }

            });

            updateStatistics();
        }


        /*
        |--------------------------------------------------------------------------
        | Toggle Favorite
        |--------------------------------------------------------------------------
        */

        function toggleFavorite(button) {
            const card =
                button.closest('.command-card');

            const commandName =
                card.dataset.command;

            let favorites =
                getFavorites();

            if (
                favorites.includes(commandName)
            ) {

                favorites =
                    favorites.filter(
                        item => item !== commandName
                    );

                card.dataset.favorite = 'false';

                button.classList.remove('active');

                button.textContent = '☆';

                button.title =
                    'Add to favorites';

            } else {

                favorites.push(commandName);

                card.dataset.favorite = 'true';

                button.classList.add('active');

                button.textContent = '★';

                button.title =
                    'Remove from favorites';
            }

            saveFavorites(favorites);

            applyFilters();

            showToast(
                favorites.includes(commandName) ?
                'Command added to favorites.' :
                'Command removed from favorites.',
                'success'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        searchInput.addEventListener(
            'input',
            applyFilters
        );


        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        filterInput.addEventListener(
            'change',
            applyFilters
        );


        /*
        |--------------------------------------------------------------------------
        | Apply Search + Filter
        |--------------------------------------------------------------------------
        */

        function applyFilters() {
            const search =
                searchInput.value
                .trim()
                .toLowerCase();

            const filter =
                filterInput.value;

            let visibleCount = 0;

            commandCards.forEach(card => {

                const command =
                    card.dataset.command;

                const description =
                    card.dataset.description;

                const type =
                    card.dataset.type;

                const favorite =
                    card.dataset.favorite === 'true';


                const matchesSearch =
                    command.includes(search) ||
                    description.includes(search);


                let matchesFilter = true;


                if (filter === 'custom') {

                    matchesFilter =
                        type === 'custom';

                }


                if (filter === 'builtin') {

                    matchesFilter =
                        type === 'builtin';

                }


                if (filter === 'favorite') {

                    matchesFilter =
                        favorite;

                }


                const shouldShow =
                    matchesSearch &&
                    matchesFilter;


                if (shouldShow) {

                    card.classList.remove('hidden');

                    visibleCount++;

                } else {

                    card.classList.add('hidden');

                }

            });


            document.getElementById(
                'visibleCommands'
            ).textContent = visibleCount;


            const emptyState =
                document.getElementById(
                    'searchEmptyState'
                );


            if (
                visibleCount === 0 &&
                commandCards.length > 0
            ) {

                emptyState.classList.add('show');

            } else {

                emptyState.classList.remove('show');

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Reset Filters
        |--------------------------------------------------------------------------
        */

        function resetFilters() {
            searchInput.value = '';

            filterInput.value = 'all';

            applyFilters();
        }


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        function updateStatistics() {
            let customCount = 0;

            let builtinCount = 0;

            commandCards.forEach(card => {

                if (
                    card.dataset.type === 'custom'
                ) {

                    customCount++;

                } else {

                    builtinCount++;

                }

            });


            document.getElementById(
                'customCommands'
            ).textContent = customCount;


            document.getElementById(
                'builtinCommands'
            ).textContent = builtinCount;


            document.getElementById(
                'totalCommands'
            ).textContent = commandCards.length;
        }


        /*
        |--------------------------------------------------------------------------
        | Run Command
        |--------------------------------------------------------------------------
        */

        async function runCommand(button) {
            const card =
                button.closest('.command-card');


            const command =
                card.dataset.command;


            /*
            |--------------------------------------------------------------------------
            | Find Actual Command Name
            |--------------------------------------------------------------------------
            */

            const commandName =
                card.querySelector(
                    '.command-name'
                ).textContent.trim();


            /*
            |--------------------------------------------------------------------------
            | Input Fields
            |--------------------------------------------------------------------------
            */

            const argsInput =
                card.querySelector(
                    '[data-role="arguments"]'
                );


            const optionsInput =
                card.querySelector(
                    '[data-role="options"]'
                );


            /*
            |--------------------------------------------------------------------------
            | Parse Arguments
            |--------------------------------------------------------------------------
            */

            const argsText =
                argsInput.value.trim();


            let args = [];


            if (argsText !== '') {

                args =
                    argsText
                    .split(/\s+/)
                    .filter(value => value !== '');

            }


            /*
            |--------------------------------------------------------------------------
            | Parse Options
            |--------------------------------------------------------------------------
            */

            const optionsText =
                optionsInput.value.trim();


            let options = {};


            if (optionsText !== '') {

                const optionParts =
                    optionsText
                    .split(',')
                    .map(
                        item => item.trim()
                    )
                    .filter(
                        item => item !== ''
                    );


                optionParts.forEach(option => {

                    option =
                        option.replace(
                            /^--/,
                            ''
                        );


                    if (
                        option.includes('=')
                    ) {

                        const parts =
                            option.split('=');


                        const key =
                            parts.shift();


                        const value =
                            parts.join('=');


                        options[key] = value;

                    } else {

                        options[option] = true;

                    }

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Loading State
            |--------------------------------------------------------------------------
            */

            const originalText =
                button.innerHTML;


            button.disabled = true;

            button.innerHTML =
                '<span class="loading-spinner"></span> Running...';


            /*
            |--------------------------------------------------------------------------
            | Output Elements
            |--------------------------------------------------------------------------
            */

            const outputBox =
                card.querySelector(
                    '[data-role="output-box"]'
                );


            const output =
                card.querySelector(
                    '[data-role="output"]'
                );


            const status =
                card.querySelector(
                    '[data-role="status"]'
                );


            const statusText =
                card.querySelector(
                    '[data-role="status-text"]'
                );


            const exitCode =
                card.querySelector(
                    '[data-role="exit-code"]'
                );


            const duration =
                card.querySelector(
                    '[data-role="duration"]'
                );


            /*
            |--------------------------------------------------------------------------
            | Show Output Box
            |--------------------------------------------------------------------------
            */

            outputBox.classList.add('show');

            output.textContent =
                'Executing command...';


            status.innerHTML =
                '<span class="text-warning">RUNNING</span>';


            statusText.textContent =
                'Running';


            /*
            |--------------------------------------------------------------------------
            | API Request
            |--------------------------------------------------------------------------
            */

            try {

                const response =
                    await fetch(
                        '/run-command', {
                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json',

                                'Accept': 'application/json',

                                'X-CSRF-TOKEN': csrfToken,

                                'X-Requested-With': 'XMLHttpRequest'
                            },

                            body: JSON.stringify({
                                command: commandName,

                                args: args,

                                options: options
                            })
                        }
                    );


                const data =
                    await response.json();


                /*
                |--------------------------------------------------------------------------
                | Success
                |--------------------------------------------------------------------------
                */

                if (
                    response.ok &&
                    data.success
                ) {

                    output.textContent =
                        data.output ||
                        'Command executed successfully with no output.';


                    exitCode.textContent =
                        data.exit_code ?? 0;


                    duration.textContent =
                        `${data.duration ?? 0} sec`;


                    statusText.textContent =
                        'Success';


                    status.innerHTML =
                        '<span class="status-success">✓ SUCCESS</span>';


                    showToast(
                        `Command "${commandName}" executed successfully.`,
                        'success'
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | Failed Command
                |--------------------------------------------------------------------------
                */
                else {

                    const errorMessage =
                        data.error ||
                        data.message ||
                        data.output ||
                        'Command execution failed.';


                    output.textContent =
                        errorMessage;


                    exitCode.textContent =
                        data.exit_code ?? 1;


                    duration.textContent =
                        `${data.duration ?? 0} sec`;


                    statusText.textContent =
                        'Failed';


                    status.innerHTML =
                        '<span class="status-failed">✕ FAILED</span>';


                    showToast(
                        `Command "${commandName}" failed.`,
                        'error'
                    );

                }


            } catch (error) {

                /*
                |--------------------------------------------------------------------------
                | Network / JavaScript Error
                |--------------------------------------------------------------------------
                */

                output.textContent =
                    error.message;


                statusText.textContent =
                    'Error';


                status.innerHTML =
                    '<span class="status-failed">✕ ERROR</span>';


                showToast(
                    'Unable to execute the command request.',
                    'error'
                );

            } finally {

                button.disabled = false;

                button.innerHTML =
                    originalText;

            }
        }


        /*
        |--------------------------------------------------------------------------
        | Toast Message
        |--------------------------------------------------------------------------
        */

        function showToast(
            message,
            type = 'success'
        ) {
            const container =
                document.getElementById(
                    'toastContainer'
                );


            const toast =
                document.createElement('div');


            toast.className =
                `custom-toast ${type}`;


            toast.innerHTML = `
            <div style="font-weight:700;margin-bottom:4px;">
                ${type === 'success'
                    ? '✓ Success'
                    : '✕ Error'}
            </div>

            <div style="color:#6b7280;font-size:13px;">
                ${escapeHtml(message)}
            </div>
        `;


            container.appendChild(toast);


            setTimeout(() => {

                toast.style.opacity = '0';

                toast.style.transform =
                    'translateX(30px)';

                toast.style.transition =
                    'all 0.3s ease';


                setTimeout(() => {

                    toast.remove();

                }, 300);

            }, 3000);
        }


        /*
        |--------------------------------------------------------------------------
        | Escape HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {
            return String(value)
                .replace(
                    /&/g,
                    '&amp;'
                )
                .replace(
                    /</g,
                    '&lt;'
                )
                .replace(
                    />/g,
                    '&gt;'
                )
                .replace(
                    /"/g,
                    '&quot;'
                )
                .replace(
                    /'/g,
                    '&#039;'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Keyboard Shortcut
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function(event) {
                /*
                |--------------------------------------------------------------------------
                | Ctrl + K = Search
                |--------------------------------------------------------------------------
                */

                if (
                    event.ctrlKey &&
                    event.key.toLowerCase() === 'k'
                ) {

                    event.preventDefault();

                    searchInput.focus();

                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Initialize Page
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'DOMContentLoaded',
            function() {
                initializeFavorites();

                updateStatistics();

                applyFilters();
            }
        );
    </script>

</body>

</html>