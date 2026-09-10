<?php

namespace App\Http\Controllers;

use App\Models\SecurityEvent;
use Illuminate\Http\Request;

class SecurityLogController extends Controller
{
    public function index()
    {
        $events = SecurityEvent::with(['agent', 'incident'])->latest('timestamp')->paginate(50);
        return view('security.logs.index', compact('events'));
    }

    public function show(SecurityEvent $event)
    {
        $event->load(['agent', 'incident']);
        return view('security.logs.show', compact('event'));
    }
}
