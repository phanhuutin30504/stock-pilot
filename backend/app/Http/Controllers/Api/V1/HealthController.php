<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $database = 'ok';

        try {
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (Throwable) {
            $database = 'error';
        }

        $payload = [
            'success' => $database === 'ok',
            'application' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
            'database' => $database,
        ];

        return response()->json($payload, $database === 'ok' ? 200 : 503);
    }
}
