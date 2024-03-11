<?php

namespace App\Http\Controllers;

use App\Jobs\JiraWebhookQueue;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // webhook data sign verification and so on
        \Log::info('webhooks', [
            'd' => $request->all(),
        ]);

        JiraWebhookQueue::dispatch($request->all());

        return response()->json([], 200);
    }
}
