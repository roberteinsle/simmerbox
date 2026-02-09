<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HouseholdController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $household = $user->household?->load(['users', 'owner']);

        return view('household.show', compact('household', 'user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Bitte gib einen Namen fuer den Haushalt ein.',
        ]);

        $user = auth()->user();

        if ($user->household_id) {
            return back()->with('error', 'Du gehoerst bereits einem Haushalt an.');
        }

        $household = Household::create([
            'name' => $request->input('name'),
            'invite_code' => Str::random(32),
            'owner_id' => $user->id,
        ]);

        $user->update(['household_id' => $household->id]);

        return redirect()->route('household.show')
            ->with('success', 'Haushalt erfolgreich erstellt.');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $household = $user->household;

        if (!$household) {
            return back()->with('error', 'Du gehoerst keinem Haushalt an.');
        }

        $this->authorize('update', $household);

        $household->update(['name' => $request->input('name')]);

        return redirect()->route('household.show')
            ->with('success', 'Haushalt aktualisiert.');
    }

    public function leave(): RedirectResponse
    {
        $user = auth()->user();
        $household = $user->household;

        if (!$household) {
            return back()->with('error', 'Du gehoerst keinem Haushalt an.');
        }

        if ($household->owner_id === $user->id) {
            return back()->with('error', 'Als Eigentuemer kannst du den Haushalt nicht verlassen. Uebertrage zuerst die Eigentuemschaft oder loesche den Haushalt.');
        }

        $user->update(['household_id' => null]);

        return redirect()->route('household.show')
            ->with('success', 'Du hast den Haushalt verlassen.');
    }

    public function regenerateInviteCode(): RedirectResponse
    {
        $user = auth()->user();
        $household = $user->household;

        if (!$household) {
            return back()->with('error', 'Du gehoerst keinem Haushalt an.');
        }

        $this->authorize('manageInvites', $household);

        $household->update(['invite_code' => Str::random(32)]);

        return redirect()->route('household.show')
            ->with('success', 'Einladungscode erneuert.');
    }

    public function destroy(): RedirectResponse
    {
        $user = auth()->user();
        $household = $user->household;

        if (!$household) {
            return back()->with('error', 'Du gehoerst keinem Haushalt an.');
        }

        $this->authorize('delete', $household);

        // Remove all members from household
        $household->users()->update(['household_id' => null]);
        $household->delete();

        return redirect()->route('household.show')
            ->with('success', 'Haushalt geloescht.');
    }
}
