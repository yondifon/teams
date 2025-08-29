<?php

namespace Malico\Teams\Actions;

use Malico\Teams\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     */
    public function delete($team): void
    {
        $team->purge();
    }
}