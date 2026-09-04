<?php

use App\Models\CommandLog;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $commands = collect(Artisan::all())
        ->map(function ($command, $name) {

            $definition = $command->getDefinition();

            return [
                'name' => $name,
                'description' => $command->getDescription(),
                'signature' => $command->getSynopsis(),
                'arguments' => collect($definition->getArguments())
                    ->map(fn($argument) => $argument->getName())
                    ->values()
                    ->toArray(),
                'options' => collect($definition->getOptions())
                    ->map(fn($option) => $option->getName())
                    ->values()
                    ->toArray(),
            ];
        })
        ->sortBy('name')
        ->values();

    return view('commands', compact('commands'));
});


/*
|--------------------------------------------------------------------------
| Run Artisan Command
|--------------------------------------------------------------------------
*/

Route::post('/run-command', function (Request $request) {

    $request->validate([
        'command' => ['required', 'string'],
        'args' => ['nullable', 'array'],
        'options' => ['nullable', 'array'],
    ]);

    $commandName = $request->command;

    try {

        /*
        |--------------------------------------------------------------------------
        | Check Command Exists
        |--------------------------------------------------------------------------
        */

        $artisanCommands = Artisan::all();

        if (!isset($artisanCommands[$commandName])) {

            return response()->json([
                'success' => false,
                'message' => 'Command not found.',
            ], 404);
        }

        $artisanCommand = $artisanCommands[$commandName];

        /*
        |--------------------------------------------------------------------------
        | Normalize Arguments
        |--------------------------------------------------------------------------
        */

        $args = $request->input('args', []);

        if (!is_array($args)) {
            $args = [];
        }

        $args = array_values(
            array_filter(
                $args,
                fn($value) => $value !== null && $value !== ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Normalize Options
        |--------------------------------------------------------------------------
        */

        $options = $request->input('options', []);

        if (!is_array($options)) {
            $options = [];
        }

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

        $definitionArguments = collect(
            $artisanCommand->getDefinition()->getArguments()
        )->values();

        foreach ($args as $index => $value) {

            if (isset($definitionArguments[$index])) {

                $argumentName = $definitionArguments[$index]->getName();

                $parameters[$argumentName] = $value;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Map Options
        |--------------------------------------------------------------------------
        */

        foreach ($options as $key => $value) {

            $key = ltrim($key, '-');

            if ($key === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Boolean Options
            |--------------------------------------------------------------------------
            */

            if (
                $value === true ||
                $value === 'true' ||
                $value === '1' ||
                $value === 1
            ) {

                $parameters['--' . $key] = true;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Empty Option
            |--------------------------------------------------------------------------
            */

            if ($value === null || $value === '') {

                $parameters['--' . $key] = true;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Option With Value
            |--------------------------------------------------------------------------
            */

            $parameters['--' . $key] = $value;
        }

        /*
        |--------------------------------------------------------------------------
        | Start Timer
        |--------------------------------------------------------------------------
        */

        $startTime = microtime(true);

        /*
        |--------------------------------------------------------------------------
        | Execute Command
        |--------------------------------------------------------------------------
        */

        $exitCode = Artisan::call(
            $commandName,
            $parameters
        );

        /*
        |--------------------------------------------------------------------------
        | Capture Output
        |--------------------------------------------------------------------------
        */

        $output = Artisan::output();

        /*
        |--------------------------------------------------------------------------
        | Calculate Duration
        |--------------------------------------------------------------------------
        */

        $duration = round(
            microtime(true) - $startTime,
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
        | Save Command History
        |--------------------------------------------------------------------------
        */

        CommandLog::create([
            'command' => $commandName,
            'arguments' => $args,
            'options' => $options,
            'exit_code' => $exitCode,
            'status' => $status,
            'output' => $output,
            'error' => $status === 'failed' ? $output : null,
            'duration' => $duration,
            'executed_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Return Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => $status === 'success',
            'status' => $status,
            'command' => $commandName,
            'exit_code' => $exitCode,
            'duration' => $duration,
            'output' => $output,
        ]);
    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Calculate Failed Duration
        |--------------------------------------------------------------------------
        */

        $duration = isset($startTime)
            ? round(microtime(true) - $startTime, 3)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Save Failed Execution
        |--------------------------------------------------------------------------
        */

        try {

            CommandLog::create([
                'command' => $commandName,
                'arguments' => $args ?? [],
                'options' => $options ?? [],
                'exit_code' => 1,
                'status' => 'failed',
                'output' => '',
                'error' => $e->getMessage(),
                'duration' => $duration,
                'executed_at' => now(),
            ]);
        } catch (Throwable $logException) {

            Log::error(
                'Unable to save command history.',
                [
                    'error' => $logException->getMessage(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Log Exception
        |--------------------------------------------------------------------------
        */

        Log::error(
            'Artisan command execution failed.',
            [
                'command' => $commandName,
                'error' => $e->getMessage(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Return Error
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => false,
            'status' => 'failed',
            'message' => $e->getMessage(),
            'error' => $e->getMessage(),
            'duration' => $duration,
        ], 500);
    }
});


/*
|--------------------------------------------------------------------------
| Command History
|--------------------------------------------------------------------------
*/

Route::get('/command-history', function (Request $request) {

    $logs = CommandLog::query()
        ->latest('executed_at')
        ->paginate(5)
        ->withQueryString();

    return view(
        'command-history',
        compact('logs')
    );
});
