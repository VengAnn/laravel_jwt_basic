<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\NotifyProcessed;

class NotifyController extends Controller
{
    public function sendNotify(Request $request)
    {
        // Handle the request data
        $name = $request->query('name');

        // Fire the NotifyProcessed event
        event(new NotifyProcessed($name));

        return response()->json(['message' => 'Notification sent successfully!', 'name' => $name]);
    }
}
