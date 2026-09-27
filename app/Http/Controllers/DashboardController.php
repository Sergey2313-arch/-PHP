<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $counts = $request->user()->tickets()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $tickets = $request->user()->tickets()->with('category')->latest()->limit(5)->get();

        return view('dashboard', compact('counts', 'tickets'));
    }
}
