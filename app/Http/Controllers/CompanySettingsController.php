<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        $company = $request->user()->company;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', 'string', Rule::in(array_keys(Company::CURRENCIES))],
            'timezone' => ['required', 'string', Rule::in(Company::timezoneIds())],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('companies/'.$company->id, 'public');
        }

        unset($data['logo'], $data['remove_logo']);

        $company->update($data);

        return redirect()->route('company.edit')->with('success', 'Company details saved.');
    }
}
