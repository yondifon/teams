<?php

namespace Malico\Teams\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    /**
     * Show the team management screen.
     *
     * @param  int  $teamId
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $teamId)
    {
        $team = Teams::newTeamModel()->findOrFail($teamId);

        if (Gate::denies('view', $team)) {
            abort(403);
        }

        return view('teams.show', [
            'user' => $request->user(),
            'team' => $team,
        ]);
    }

    /**
     * Show the team creation screen.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Teams::newTeamModel());

        return view('teams.create', [
            'user' => $request->user(),
        ]);
    }
}
