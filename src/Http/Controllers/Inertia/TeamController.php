<?php

namespace Malico\Teams\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Malico\Teams\Actions\ValidateTeamDeletion;
use Malico\Teams\Contracts\CreatesTeams;
use Malico\Teams\Contracts\DeletesTeams;
use Malico\Teams\Contracts\UpdatesTeamNames;
use Malico\Teams\RedirectsActions;

class TeamController extends Controller
{
    use RedirectsActions;

    /**
     * Show the team management screen.
     *
     * @param  int  $teamId
     * @return \Inertia\Response
     */
    public function show(Request $request, $teamId)
    {
        $team = Teams::newTeamModel()->findOrFail($teamId);

        Gate::authorize('view', $team);

        return Teams::inertia()->render($request, 'Teams/Show', [
            'team' => $team->load('owner', 'users', 'invitations'),
            'availableRoles' => array_values(Teams::$roles),
            'availablePermissions' => Teams::$permissions,
            'defaultPermissions' => Teams::$defaultPermissions,
            'permissions' => [
                'canAddTeamMembers' => Gate::check('addTeamMember', $team),
                'canDeleteTeam' => Gate::check('delete', $team),
                'canRemoveTeamMembers' => Gate::check('removeTeamMember', $team),
                'canUpdateTeam' => Gate::check('update', $team),
                'canUpdateTeamMembers' => Gate::check('updateTeamMember', $team),
            ],
        ]);
    }

    /**
     * Show the team creation screen.
     *
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Teams::newTeamModel());

        return Teams::inertia()->render($request, 'Teams/Create');
    }

    /**
     * Create a new team.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $creator = app(CreatesTeams::class);

        $creator->create($request->user(), $request->all());

        return $this->redirectPath($creator);
    }

    /**
     * Update the given team's name.
     *
     * @param  int  $teamId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $teamId)
    {
        $team = Teams::newTeamModel()->findOrFail($teamId);

        app(UpdatesTeamNames::class)->update($request->user(), $team, $request->all());

        return back(303);
    }

    /**
     * Delete the given team.
     *
     * @param  int  $teamId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $teamId)
    {
        $team = Teams::newTeamModel()->findOrFail($teamId);

        app(ValidateTeamDeletion::class)->validate($request->user(), $team);

        $deleter = app(DeletesTeams::class);

        $deleter->delete($team);

        return $this->redirectPath($deleter);
    }
}
