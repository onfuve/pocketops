<?php

namespace App\Http\Controllers;

use App\Models\LeadChannel;
use Illuminate\Http\Request;

class LeadChannelController extends Controller
{
    public function index()
    {
        $channels = LeadChannel::orderBy('sort')->get();

        return view('settings.lead-channels', compact('channels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_referral' => 'nullable|boolean',
        ]);
        $validated['is_referral'] = $request->boolean('is_referral');
        $validated['sort'] = (int) (LeadChannel::max('sort') ?? 0) + 1;

        LeadChannel::create($validated);

        return redirect()->route('settings.lead-channels')->with('success', 'کانال اضافه شد.');
    }

    public function destroy(LeadChannel $leadChannel)
    {
        $leadChannel->delete();

        return redirect()->route('settings.lead-channels')->with('success', 'کانال حذف شد.');
    }
}
