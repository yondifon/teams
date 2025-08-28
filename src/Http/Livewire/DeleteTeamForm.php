<?php

namespace Malico\Teams\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Malico\Teams\Actions\ValidateTeamDeletion;
use Malico\Teams\Contracts\DeletesTeams;
use Malico\Teams\RedirectsActions;

class DeleteTeamForm extends Component
{
    use RedirectsActions;

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Indicates if team deletion is being confirmed.
     *
     * @var bool
     */
    public $confirmingTeamDeletion = false;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
    }

    /**
     * Delete the team.
     *
     * @return mixed
     */
    public function deleteTeam(ValidateTeamDeletion $validator, DeletesTeams $deleter)
    {
        $validator->validate(Auth::user(), $this->team);

        $deleter->delete($this->team);

        $this->team = null;

        return $this->redirectPath($deleter);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.delete-team-form');
    }
}
