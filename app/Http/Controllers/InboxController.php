<?php

namespace App\Http\Controllers;

use App\Inbox;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InboxController extends Controller
{
    public function CatchAll(Request $request) {
        $inbox = new Inbox($request->all());
        $inbox['files'] = $request->allFiles();
        $inbox->save();
        return new Response("Message received and saved");
    }
}
