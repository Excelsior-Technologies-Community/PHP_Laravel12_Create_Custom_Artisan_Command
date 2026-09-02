<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

Route::get('/', function () {
    $commands = collect(Artisan::all())
        ->map(function ($command, $key) {
            return [
                'name' => $key,
                'description' => method_exists($command, 'getDescription') ? $command->getDescription() : '',
                'signature' => method_exists($command, 'getSignature') ? $command->getSignature() : '',
                'arguments' => method_exists($command, 'getDefinition') ? array_keys($command->getDefinition()->getArguments()) : [],
                'options' => method_exists($command, 'getDefinition') ? array_keys($command->getDefinition()->getOptions()) : [],
            ];
        })
        ->sortBy('name')
        ->values()
        ->all();

    return view('commands', compact('commands'));
});

Route::post('/run-command', function (Request $request) {
    $command = $request->input('command');
    $args = $request->input('args', []);
    $options = $request->input('options', []);

    if (!$command) {
        return response()->json(['error' => 'Command is required'], 400);
    }

    try {
        $artisanCommand = Artisan::all()[$command] ?? null;

        $parameters = [];

        if ($artisanCommand && method_exists($artisanCommand, 'getDefinition')) {
            $definition = $artisanCommand->getDefinition();
            $argumentNames = array_keys($definition->getArguments());

            if (is_array($args)) {
                foreach ($args as $index => $value) {
                    if (isset($argumentNames[$index])) {
                        $parameters[$argumentNames[$index]] = $value;
                    }
                }
            }
        } elseif (is_array($args)) {
            $parameters = array_filter($args, fn ($v) => $v !== '' && $v !== null);
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if ($value !== '' && $value !== null) {
                    $paramKey = str_starts_with($key, '--') ? $key : '--' . $key;
                    $parameters[$paramKey] = $value;
                }
            }
        }

        $start = microtime(true);
        $exitCode = Artisan::call($command, $parameters);
        $output = Artisan::output();
        $duration = round(microtime(true) - $start, 3);

        return response()->json([
            'success' => $exitCode === 0,
            'exit_code' => $exitCode,
            'output' => $output,
            'duration' => $duration . 's',
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'exit_code' => 1,
            'error' => $e->getMessage(),
        ], 500);
    }
});
