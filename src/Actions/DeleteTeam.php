<?php

namespace Malico\Teams\Actions;

use Malico\Teams\Contracts\DeletesTeams;
use Malico\Teams\Contracts\ValidatesTeamDeletion;

class DeleteTeam implements DeletesTeams
{
    public function __construct(
        protected ValidatesTeamDeletion $validates
    ) {}

    /**
     * Delete the given team.
     */
    public function delete($user, $team): void
    {
        $this->validates->validate($user, $team);

        $team->purge();
    }
}
