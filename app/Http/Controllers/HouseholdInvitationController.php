<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HouseholdInvitationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Bitte gib eine E-Mail-Adresse ein.',
            'email.email' => 'Bitte gib eine gueltige E-Mail-Adresse ein.',
        ]);

        $user = auth()->user();
        $household = $user->household;

        if (!$household) {
            return back()->with('error', 'Du gehoerst keinem Haushalt an.');
        }

        $invitation = HouseholdInvitation::create([
            'household_id' => $household->id,
            'email' => $request->input('email'),
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        return back()->with('success', 'Einladung erstellt. Teile folgenden Link: ' . route('household.join', $invitation->token));
    }

    public function accept(string $token): RedirectResponse|View
    {
        $invitation = HouseholdInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = auth()->user();

        if ($user->household_id) {
            return redirect()->route('household.show')
                ->with('error', 'Du gehoerst bereits einem Haushalt an. Verlasse zuerst deinen aktuellen Haushalt.');
        }

        $user->update(['household_id' => $invitation->household_id]);
        $invitation->update(['accepted_at' => now()]);

        return redirect()->route('household.show')
            ->with('success', 'Du bist dem Haushalt beigetreten.');
    }

    public function joinViaCode(Request $request): RedirectResponse
    {
        $request->validate([
            'invite_code' => ['required', 'string'],
        ]);

        $household = Household::where('invite_code', $request->input('invite_code'))->first();

        if (!$household) {
            return back()->with('error', 'Ungueltiger Einladungscode.');
        }

        $user = auth()->user();

        if ($user->household_id) {
            return back()->with('error', 'Du gehoerst bereits einem Haushalt an.');
        }

        $user->update(['household_id' => $household->id]);

        return redirect()->route('household.show')
            ->with('success', 'Du bist dem Haushalt "' . $household->name . '" beigetreten.');
    }
}
