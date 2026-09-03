<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laravel Artisan Command Runner</title>

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family:
                'Segoe UI',
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #667eea 0%,
                    #764ba2 100%
                );

            min-height: 100vh;

            padding: 20px;
        }


        .container {

            max-width: 1200px;

            margin: 0 auto;
        }


        .header {

            text-align: center;

            color: white;

            margin-bottom: 30px;
        }


        .header h1 {

            font-size: 2.5em;

            margin-bottom: 10px;

            text-shadow:
                2px 2px 4px
                rgba(0, 0, 0, 0.3);
        }


        .header p {

            font-size: 1.1em;

            opacity: 0.9;
        }


        .commands-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(350px, 1fr)
                );

            gap: 20px;

            margin-bottom: 30px;
        }


        .command-card {

            background: white;

            border-radius: 12px;

            padding: 20px;

            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, 0.2);

            transition:
                transform 0.2s,
                box-shadow 0.2s;

            border-left:
                4px solid #667eea;
        }


        .command-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 15px 40px
                rgba(0, 0, 0, 0.3);
        }


        .command-header {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            gap: 10px;

            margin-bottom: 12px;
        }


        .command-name {

            font-family:
                'Courier New',
                monospace;

            font-size: 1.1em;

            font-weight: bold;

            color: #667eea;

            background: #f0f4ff;

            padding: 6px 12px;

            border-radius: 6px;

            flex: 1;

            word-break: break-word;
        }


        .command-description {

            color: #666;

            font-size: 0.95em;

            margin-bottom: 8px;

            min-height: 40px;
        }


        .command-signature {

            font-family:
                'Courier New',
                monospace;

            font-size: 0.85em;

            color: #888;

            background: #f9f9f9;

            padding: 6px 10px;

            border-radius: 4px;

            margin-bottom: 15px;

            word-break: break-all;
        }


        .command-form {

            display: flex;

            flex-direction: column;

            gap: 10px;
        }


        .form-group {

            display: flex;

            flex-direction: column;

            gap: 5px;
        }


        .form-group label {

            font-size: 0.85em;

            color: #555;

            font-weight: 600;
        }


        .form-group input,
        .form-group select {

            padding:
                8px 12px;

            border:
                1px solid #ddd;

            border-radius: 6px;

            font-size: 0.95em;

            font-family:
                'Courier New',
                monospace;
        }


        .form-group input:focus,
        .form-group select:focus {

            outline: none;

            border-color:
                #667eea;

            box-shadow:
                0 0 0 3px
                rgba(
                    102,
                    126,
                    234,
                    0.1
                );
        }


        .btn {

            background:
                linear-gradient(
                    135deg,
                    #667eea 0%,
                    #764ba2 100%
                );

            color: white;

            border: none;

            padding:
                10px 20px;

            border-radius: 6px;

            cursor: pointer;

            font-size: 0.95em;

            font-weight: 600;

            transition:
                opacity 0.2s;
        }


        .btn:hover {

            opacity: 0.9;
        }


        .btn:disabled {

            opacity: 0.5;

            cursor:
                not-allowed;
        }


        .output-section {

            background: white;

            border-radius: 12px;

            padding: 20px;

            box-shadow:
                0 10px 30px
                rgba(0, 0, 0, 0.2);

            margin-top: 20px;
        }


        .output-header {

            display: flex;

            justify-content:
                space-between;

            align-items: center;

            margin-bottom: 15px;

            padding-bottom: 10px;

            border-bottom:
                2px solid #f0f0f0;

            gap: 15px;
        }


        .output-title {

            font-size: 1.3em;

            color: #333;

            font-weight: bold;
        }


        .output-meta {

            display: flex;

            gap: 15px;

            font-size: 0.9em;

            color: #666;
        }


        .output-meta span {

            padding:
                4px 10px;

            background:
                #f5f5f5;

            border-radius: 4px;
        }


        .output-content {

            background:
                #1e1e1e;

            color:
                #d4d4d4;

            padding: 15px;

            border-radius: 8px;

            font-family:
                'Courier New',
                monospace;

            font-size: 0.9em;

            line-height: 1.6;

            max-height: 500px;

            overflow-y: auto;

            white-space: pre-wrap;

            word-wrap: break-word;
        }


        .output-content.success {

            border-left:
                4px solid #4caf50;
        }


        .output-content.error {

            border-left:
                4px solid #f44336;
        }


        .hidden {

            display: none;
        }


        .spinner {

            display: inline-block;

            width: 16px;

            height: 16px;

            border:
                2px solid
                rgba(
                    255,
                    255,
                    255,
                    0.3
                );

            border-top-color:
                white;

            border-radius: 50%;

            animation:
                spin 0.8s
                linear infinite;
        }


        @keyframes spin {

            to {
                transform:
                    rotate(360deg);
            }
        }


        .badge {

            display: inline-block;

            padding:
                2px 8px;

            border-radius: 4px;

            font-size: 0.75em;

            font-weight: bold;

            text-transform:
                uppercase;

            white-space: nowrap;
        }


        .badge-custom {

            background:
                #ff9800;

            color: white;
        }


        .badge-builtin {

            background:
                #e0e0e0;

            color: #555;
        }


        @media (max-width: 768px) {

            .commands-grid {

                grid-template-columns:
                    1fr;
            }


            .header h1 {

                font-size:
                    1.8em;
            }


            .output-header {

                flex-direction:
                    column;

                align-items:
                    flex-start;
            }


            .output-meta {

                flex-wrap: wrap;
            }
        }

    </style>

</head>


<body>

<div class="container">


    <!-- Header -->

    <div class="header">

        <h1>
            🚀 Laravel Artisan Command Runner
        </h1>

        <p>
            Run Artisan commands directly from your browser
        </p>

    </div>


    <!-- Commands -->

    <div
        class="commands-grid"
        id="commandsGrid"
    >

        @foreach($commands as $command)

            <div class="command-card">


                <!-- Command Header -->

                <div class="command-header">

                    <div class="command-name">
                        {{ $command['name'] }}
                    </div>


                    @if(
                        \Illuminate\Support\Str::startsWith(
                            $command['name'],
                            [
                                'create:',
                                'delete:',
                                'list:',
                                'user:',
                                'db:',
                                'cache:',
                                'schedule:',
                                'app:',
                                'command:',
                            ]
                        )
                    )

                        <span
                            class="badge badge-custom"
                        >
                            Custom
                        </span>

                    @else

                        <span
                            class="badge badge-builtin"
                        >
                            Built-in
                        </span>

                    @endif

                </div>


                <!-- Description -->

                <div class="command-description">

                    {{
                        $command['description']
                        ?: 'No description available'
                    }}

                </div>


                <!-- Signature -->

                @if(!empty($command['signature']))

                    <div class="command-signature">

                        {{ $command['signature'] }}

                    </div>

                @endif


                <!-- Form -->

                <form
                    class="command-form"
                    onsubmit="runCommand(
                        event,
                        @js($command['name'])
                    )"
                >


                    <!-- Arguments -->

                    @if(!empty($command['arguments']))

                        <div class="form-group">

                            <label>

                                Arguments
                                ({{
                                    implode(
                                        ', ',
                                        $command['arguments']
                                    )
                                }})

                            </label>

                            <input
                                type="text"
                                placeholder="e.g. 5"
                                data-args
                            >

                        </div>

                    @endif


                    <!-- Options -->

                    @if(!empty($command['options']))

                        <div class="form-group">

                            <label>

                                Options
                                ({{
                                    implode(
                                        ', ',
                                        $command['options']
                                    )
                                }})

                            </label>

                            <input
                                type="text"
                                placeholder="e.g. --force, --verified"
                                data-options
                            >

                        </div>

                    @endif


                    @if(
                        empty($command['arguments']) &&
                        empty($command['options'])
                    )

                        <div class="form-group">

                            <label>
                                No arguments or options needed
                            </label>

                        </div>

                    @endif


                    <!-- Run Button -->

                    <button
                        type="submit"
                        class="btn"
                    >

                        ▶ Run Command

                    </button>


                </form>

            </div>

        @endforeach

    </div>


    <!-- Output -->

    <div
        class="output-section hidden"
        id="outputSection"
    >

        <div class="output-header">


            <div class="output-title">
                📋 Command Output
            </div>


            <div class="output-meta">

                <span id="exitCode">
                    Exit Code: -
                </span>

                <span id="duration">
                    Duration: -
                </span>

            </div>


        </div>


        <div
            class="output-content"
            id="outputContent"
        ></div>


    </div>


</div>


<script>

async function runCommand(event, commandName) {

    event.preventDefault();


    /*
    |--------------------------------------------------------------------------
    | Get Form Elements
    |--------------------------------------------------------------------------
    */

    const card =
        event.target.closest('.command-card');

    const argsInput =
        card.querySelector('[data-args]');

    const optionsInput =
        card.querySelector('[data-options]');

    const btn =
        card.querySelector('.btn');


    /*
    |--------------------------------------------------------------------------
    | Get Input Values
    |--------------------------------------------------------------------------
    */

    const argsStr =
        argsInput
            ? argsInput.value.trim()
            : '';


    const optionsStr =
        optionsInput
            ? optionsInput.value.trim()
            : '';


    /*
    |--------------------------------------------------------------------------
    | Convert Arguments
    |--------------------------------------------------------------------------
    */

    const args =
        argsStr
            ? argsStr.split(/\s+/)
            : [];


    /*
    |--------------------------------------------------------------------------
    | Convert Options
    |--------------------------------------------------------------------------
    */

    const options = {};


    if (optionsStr) {

        optionsStr
            .split(',')
            .forEach(option => {

                let opt =
                    option.trim();


                if (!opt) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | --option=value
                |--------------------------------------------------------------------------
                */

                if (
                    opt.startsWith('--')
                ) {

                    opt =
                        opt.substring(2);

                }


                /*
                |--------------------------------------------------------------------------
                | Option With Value
                |--------------------------------------------------------------------------
                */

                if (opt.includes('=')) {

                    const parts =
                        opt.split('=');

                    const key =
                        parts.shift().trim();

                    const value =
                        parts
                            .join('=')
                            .trim();


                    options[key] =
                        value || true;


                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Boolean Option
                |--------------------------------------------------------------------------
                */

                options[opt] = true;

            });
    }


    /*
    |--------------------------------------------------------------------------
    | Disable Button
    |--------------------------------------------------------------------------
    */

    btn.disabled = true;

    btn.innerHTML =
        '<span class="spinner"></span> Running...';


    /*
    |--------------------------------------------------------------------------
    | Output Elements
    |--------------------------------------------------------------------------
    */

    const outputSection =
        document.getElementById(
            'outputSection'
        );


    const outputContent =
        document.getElementById(
            'outputContent'
        );


    const exitCodeSpan =
        document.getElementById(
            'exitCode'
        );


    const durationSpan =
        document.getElementById(
            'duration'
        );


    /*
    |--------------------------------------------------------------------------
    | Show Output Section
    |--------------------------------------------------------------------------
    */

    outputSection.classList.remove(
        'hidden'
    );


    outputContent.textContent =
        'Running command...';


    outputContent.className =
        'output-content';


    /*
    |--------------------------------------------------------------------------
    | Execute AJAX Request
    |--------------------------------------------------------------------------
    */

    try {

        const response =
            await fetch(
                '{{ url('/run-command') }}',
                {
                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content || '',

                    },

                    body: JSON.stringify({

                        command:
                            commandName,

                        args:
                            args,

                        options:
                            options,

                    }),

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Parse Response
        |--------------------------------------------------------------------------
        */

        const result =
            await response.json();


        /*
        |--------------------------------------------------------------------------
        | Successful Command
        |--------------------------------------------------------------------------
        */

        if (result.success) {

            outputContent.textContent =
                result.output ||
                'Command executed successfully (no output)';


            outputContent.classList.add(
                'success'
            );


            exitCodeSpan.textContent =
                'Exit Code: ' +
                result.exit_code;


            exitCodeSpan.style.color =
                '#4caf50';


        }


        /*
        |--------------------------------------------------------------------------
        | Failed Command
        |--------------------------------------------------------------------------
        */

        else {

            outputContent.textContent =
                (result.output || '') +
                (
                    result.error
                        ? '\nError: ' +
                          result.error
                        : ''
                );


            outputContent.classList.add(
                'error'
            );


            exitCodeSpan.textContent =
                'Exit Code: ' +
                (
                    result.exit_code ?? 1
                );


            exitCodeSpan.style.color =
                '#f44336';

        }


        /*
        |--------------------------------------------------------------------------
        | Duration
        |--------------------------------------------------------------------------
        */

        durationSpan.textContent =
            'Duration: ' +
            (
                result.duration || '-'
            );


    } catch (error) {


        /*
        |--------------------------------------------------------------------------
        | AJAX / Network Error
        |--------------------------------------------------------------------------
        */

        outputContent.textContent =
            'Error: ' +
            error.message;


        outputContent.classList.add(
            'error'
        );


        exitCodeSpan.textContent =
            'Exit Code: 1';


        exitCodeSpan.style.color =
            '#f44336';


        durationSpan.textContent =
            'Duration: -';

    } finally {


        /*
        |--------------------------------------------------------------------------
        | Restore Button
        |--------------------------------------------------------------------------
        */

        btn.disabled = false;

        btn.innerHTML =
            '▶ Run Command';

    }

}

</script>


</body>

</html>