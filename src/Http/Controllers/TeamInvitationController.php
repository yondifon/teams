<?php

namespace Malico\Teams\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Malico\Teams\Contracts\AddsTeamMembers;
use Malico\Teams\Teams;

class TeamInvitationController extends Controller
{
    /**
     * Handle team invitation URL and redirect appropriately.
     *
     * @param  int  $invitationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, $invitationId)
    {
        $model = Teams::teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->firstOrFail();

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();

            return redirect('/')->with('error', __('This invitation has expired.'));
        }

        if (! auth()->check()) {
            return $this->handleUnauthenticatedUser($invitation);
        }

        if (auth()->user()->email !== $invitation->email) {
            Auth::logout();

            return redirect()->signedRoute('team-invitations.show', $invitation);
        }

        return redirect()->signedRoute('team-invitations.accept', $invitation);
    }

    protected function handleUnauthenticatedUser($invitation)
    {
        $userModel = Teams::userModel();
        $userExists = $userModel::where('email', $invitation->email)->exists();

        if ($userExists) {
            return redirect()->signedRoute('login', [
                'invitation' => $invitation->id
            ])->with('message', __('Please sign in to accept your team invitation.'));
        }

        return redirect()->signedRoute('register', [
            'invitation' => $invitation->id
        ])->with('message', __('Create an account to join the team.'));
    }

    /**
     * Accept a team invitation.
     *
     * @param  int  $invitationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Request $request, $invitationId)
    {
        $model = Teams::teamInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        app(AddsTeamMembers::class)->add(
            $invitation->team->owner,
            $invitation->team,
            $invitation->email,
            $invitation->role
        );

        $invitation->delete();

        return redirect(config('teams.home', '/dashboard'))->banner(
            __('Great! You have accepted the invitation to join the :team team.', ['team' => $invitation->team->name]),
        );
    }

    /**
     * Cancel the given team invitation.
     *
     * @param  int  $invitationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $invitationId)
    {
        $model = Teams::teamInvitationModel();

        $invitation = $model::whereKey($invitationId)->firstOrFail();

        if (! Gate::forUser($request->user())->check('removeTeamMember', $invitation->team)) {
            throw new AuthorizationException;
        }

        $invitation->delete();

        return back(303);
    }
}
