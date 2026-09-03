<?php

use App\Models\CommandLog;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $commands = collect(Artisan::all())
        ->map(function ($command, $key) {

            return [
                'name' => $key,

                'description' => method_exists($command, 'getDescription')
                    ? $command->getDescription()
                    : '',

                'signature' => method_exists($command, 'getSignature')
                    ? $command->getSignature()
                    : '',

                'arguments' => method_exists($command, 'getDefinition')
                    ? array_keys(
                        $command
                            ->getDefinition()
                            ->getArguments()
                    )
                    : [],

                'options' => method_exists($command, 'getDefinition')
                    ? array_keys(
                        $command
                            ->getDefinition()
                            ->getOptions()
                    )
                    : [],
            ];
        })
        ->sortBy('name')
        ->values()
        ->all();

    return view('commands', compact('commands'));
});


/*
|--------------------------------------------------------------------------
| Run Artisan Command
|--------------------------------------------------------------------------
*/

Route::post('/run-command', function (Request $request) {

    $command = $request->input('command');

    $args = $request->input('args', []);

    $options = $request->input('options', []);


    /*
    |--------------------------------------------------------------------------
    | Validate Command
    |--------------------------------------------------------------------------
    */

    if (!$command) {

        return response()->json([
            'success' => false,
            'exit_code' => 1,
            'output' => '',
            'error' => 'Command is required',
            'duration' => '-',
        ], 400);
    }


    /*
    |--------------------------------------------------------------------------
    | Find Artisan Command
    |--------------------------------------------------------------------------
    */

    $artisanCommand = Artisan::all()[$command] ?? null;

    if (!$artisanCommand) {

        return response()->json([
            'success' => false,
            'exit_code' => 1,
            'output' => '',
            'error' => "Command '{$command}' does not exist.",
            'duration' => '-',
        ], 404);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Input
    |--------------------------------------------------------------------------
    */

    $args = is_array($args)
        ? array_values($args)
        : [];

    $options = is_array($options)
        ? $options
        : [];


    try {

        /*
        |--------------------------------------------------------------------------
        | Build Artisan Parameters
        |--------------------------------------------------------------------------
        */

        $parameters = [];


        /*
        |--------------------------------------------------------------------------
        | Map Positional Arguments
        |--------------------------------------------------------------------------
        */

        if (method_exists($artisanCommand, 'getDefinition')) {

            $definition = $artisanCommand->getDefinition();

            $argumentNames = array_keys(
                $definition->getArguments()
            );


            foreach ($args as $index => $value) {

                if (
                    isset($argumentNames[$index]) &&
                    $value !== '' &&
                    $value !== null
                ) {

                    $parameters[
                        $argumentNames[$index]
                    ] = $value;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Map Command Options
        |--------------------------------------------------------------------------
        */

        foreach ($options as $key => $value) {

            if ($value === '' || $value === null) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Remove -- Prefix
            |--------------------------------------------------------------------------
            */

            $key = ltrim($key, '-');


            /*
            |--------------------------------------------------------------------------
            | Boolean Options
            |--------------------------------------------------------------------------
            */

            if (
                $value === true ||
                $value === 'true' ||
                $value === '1'
            ) {

                $parameters[
                    '--' . $key
                ] = true;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Value Options
            |--------------------------------------------------------------------------
            */

            $parameters[
                '--' . $key
            ] = $value;
        }


        /*
        |--------------------------------------------------------------------------
        | Execute Command
        |--------------------------------------------------------------------------
        */

        $start = microtime(true);

        $exitCode = Artisan::call(
            $command,
            $parameters
        );

        $output = Artisan::output();

        $duration = round(
            microtime(true) - $start,
            3
        );


        /*
        |--------------------------------------------------------------------------
        | Determine Status
        |--------------------------------------------------------------------------
        */

        $status = $exitCode === Command::SUCCESS
            ? 'success'
            : 'failed';


        /*
        |--------------------------------------------------------------------------
        | Save Command Execution History
        |--------------------------------------------------------------------------
        */

        CommandLog::create([

            'command' => $command,

            'arguments' => $args,

            'options' => $options,

            'exit_code' => $exitCode,

            'status' => $status,

            'output' => $output,

            'error' => $exitCode !== Command::SUCCESS
                ? $output
                : null,

            'duration' => $duration,

            'executed_at' => now(),

        ]);


        /*
        |--------------------------------------------------------------------------
        | Return Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => $exitCode === Command::SUCCESS,

            'exit_code' => $exitCode,

            'output' => $output,

            'error' => $exitCode !== Command::SUCCESS
                ? $output
                : null,

            'duration' => $duration . 's',

        ]);


    } catch (\Throwable $e) {


        /*
        |--------------------------------------------------------------------------
        | Calculate Duration
        |--------------------------------------------------------------------------
        */

        $duration = isset($start)
            ? round(
                microtime(true) - $start,
                3
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Save Exception To History
        |--------------------------------------------------------------------------
        */

        try {

            CommandLog::create([

                'command' => $command,

                'arguments' => $args,

                'options' => $options,

                'exit_code' => 1,

                'status' => 'failed',

                'output' => '',

                'error' => $e->getMessage(),

                'duration' => $duration,

                'executed_at' => now(),

            ]);

        } catch (\Throwable) {

            /*
            |--------------------------------------------------------------------------
            | Do Not Hide Original Error
            |--------------------------------------------------------------------------
            */

        }


        /*
        |--------------------------------------------------------------------------
        | Return Error Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => false,

            'exit_code' => 1,

            'output' => '',

            'error' => $e->getMessage(),

            'duration' => $duration !== null
                ? $duration . 's'
                : '-',

        ], 500);
    }
});


/*
|--------------------------------------------------------------------------
| Command History Page
|--------------------------------------------------------------------------
*/

Route::get('/command-history', function () {

    $logs = CommandLog::query()
        ->orderByDesc('executed_at')
        ->paginate(10);

    return view(
        'command-history',
        compact('logs')
    );
});