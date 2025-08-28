<?php

namespace Malico\Teams\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CurrentTeamController extends Controller
{
    /**
     * Update the authenticated user's current team.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $team = Teams::newTeamModel()->findOrFail($request->team_id);

        if (! $request->user()->switchTeam($team)) {
            abort(403);
        }

        return redirect(config('teams.home', '/dashboard'), 303);
    }
}
