<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Artisan Command History</title>

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;

            background: linear-gradient(
                135deg,
                #667eea,
                #764ba2
            );

            min-height: 100vh;
            padding: 30px;
        }

        .container {
            max-width: 1300px;
            margin: auto;
        }

        .header {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 25px;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 20px;
        }

        .top-bar h2 {
            color: #333;
            margin: 0;
        }

        .btn-runner {
            text-decoration: none;
            padding: 10px 18px;

            background: #667eea;
            color: white;

            border-radius: 7px;
            font-weight: bold;

            transition: 0.2s ease;
        }

        .btn-runner:hover {
            background: #5568d9;
            color: white;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background: #f4f6ff;
            color: #444;

            padding: 14px;
            text-align: left;

            white-space: nowrap;
        }

        td {
            padding: 13px;

            border-bottom: 1px solid #eee;

            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8f9ff;
        }

        .command {
            font-family: monospace;
            font-weight: bold;
            color: #667eea;
        }

        .success {
            color: #198754;
            font-weight: bold;
        }

        .failed {
            color: #dc3545;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }

        /*
        |--------------------------------------------------------------------------
        | Bootstrap Pagination
        |--------------------------------------------------------------------------
        */

        .pagination-wrapper {
            margin-top: 25px;
        }

        .pagination-wrapper .pagination {
            margin-bottom: 0;
        }

        .pagination-wrapper .page-link {
            color: #667eea;
        }

        .pagination-wrapper .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }

        .pagination-wrapper .page-link:hover {
            color: #5568d9;
        }

        .pagination-wrapper .page-item.active .page-link:hover {
            color: white;
        }

        @media (max-width: 768px) {

            body {
                padding: 15px;
            }

            .card {
                padding: 18px;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header h1 {
                font-size: 26px;
            }

            .header p {
                font-size: 14px;
            }

        }

    </style>
</head>

<body>

<div class="container">

    <!-- Header -->
    <div class="header">

        <h1>
            📜 Artisan Command History
        </h1>

        <p>
            Track commands executed through the Laravel Artisan Runner
        </p>

    </div>


    <!-- Main Card -->
    <div class="card">

        <!-- Top Bar -->
        <div class="top-bar">

            <h2>
                Execution History
            </h2>

            <a
                href="{{ url('/') }}"
                class="btn-runner"
            >
                ← Command Runner
            </a>

        </div>


        @if($logs->count())

            <!-- Table -->
            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Command
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Exit Code
                            </th>

                            <th>
                                Duration
                            </th>

                            <th>
                                Executed At
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($logs as $log)

                            <tr>

                                <!-- Command -->
                                <td class="command">
                                    {{ $log->command }}
                                </td>


                                <!-- Status -->
                                <td>

                                    @if($log->status === 'success')

                                        <span class="success">
                                            ✅ SUCCESS
                                        </span>

                                    @else

                                        <span class="failed">
                                            ❌ FAILED
                                        </span>

                                    @endif

                                </td>


                                <!-- Exit Code -->
                                <td>
                                    {{ $log->exit_code }}
                                </td>


                                <!-- Duration -->
                                <td>
                                    {{ $log->duration }}s
                                </td>


                                <!-- Executed At -->
                                <td>
                                    {{ $log->executed_at?->format('Y-m-d H:i:s') }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <!-- Bootstrap Pagination -->
            <div class="pagination-wrapper">

                {{ $logs->links() }}

            </div>


        @else

            <!-- Empty State -->
            <div class="empty">

                📭 No command execution history found.

            </div>

        @endif

    </div>

</div>


<!-- Bootstrap 5 JS -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>