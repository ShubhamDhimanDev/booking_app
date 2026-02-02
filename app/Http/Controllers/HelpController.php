<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHelpRequest;
use App\Models\HelpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index()
    {
        return view('static.help');
    }

    public function store(StoreHelpRequest $request): RedirectResponse
    {
        $data = $request->validated();

        HelpRequest::create($data);

        return redirect()->route('help')->with('status', 'Thanks — your message has been received. We will respond shortly.');
    }
}
