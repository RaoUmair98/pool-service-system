<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * The signed-in admin's own company details.
 */
class CompanySettingsController extends Controller
{
    public function edit(Request $request)
    {
        return view('company.edit', ['company' => $request->user()->company]);
    }

    public function update(Request $request)
    {
        $request->user()->company->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]));

        return redirect()->route('company.edit')->with('success', 'Company details saved.');
    }
}
