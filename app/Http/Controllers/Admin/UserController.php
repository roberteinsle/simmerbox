<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('household')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        // Sich selbst kann man nicht degradieren
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Du kannst deinen eigenen Admin-Status nicht aendern.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? 'zum Admin ernannt' : 'als Admin entfernt';

        return back()->with('success', "{$user->name} wurde {$status}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Du kannst dich nicht selbst loeschen.');
        }

        // Haushalt-Eigentuemerschaft uebertragen falls noetig
        if ($user->household_id) {
            $household = $user->household;
            if ($household && $household->owner_id === $user->id) {
                // Naechsten User im Haushalt zum Owner machen
                $nextOwner = $household->users()->where('id', '!=', $user->id)->first();
                if ($nextOwner) {
                    $household->update(['owner_id' => $nextOwner->id]);
                } else {
                    $household->delete();
                }
            }
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Benutzer \"{$name}\" geloescht.");
    }
}
