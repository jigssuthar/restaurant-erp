<?php

namespace App\Http\Controllers;

use App\Models\ContactEnquiry;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:20',
            'business_type' => 'required|in:Restaurant,Cafe,Cloud Kitchen,Restaurant Chain,Other',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:3000',
        ]);

        ContactEnquiry::create($validated);

        return redirect()
            ->route('contact')
            ->with('success', 'Thank you! Your enquiry has been submitted successfully.');
    }
}