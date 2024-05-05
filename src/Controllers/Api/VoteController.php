<?php

namespace Azuriom\Plugin\Vote\Controllers\Api;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\Vote\Models\Site;
use Azuriom\Plugin\Vote\Models\Vote;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VoteController extends Controller
{
    /**
     * Display the vote home page.
     */
    public function index(Request $request)
    {
        $userIds = $request->array('users');
        $sites = Site::enabled()->get();
        $topVotes = Vote::getTopVoters(now()->startOfMonth());

        $users = User::findMany($userIds)->map(function (User $user) use ($sites, $topVotes) {
            $voteTimes = $sites->mapWithKeys(fn (Site $site) => [
                $site->id => $site->getNextVoteTime($user)?->toIso8601String(),
            ]);
            $topVote = $topVotes->firstWhere('user_id', $user->id);

            return [
                'votes' => $this->getVotesCount($user),
                'sites' => $voteTimes,
                'ranking' => $topVote ? $topVote->position : -1,
            ];
        });

        $topVotes = $topVotes->map(fn ($vote) => [
            'id' => $vote->user->id,
            'name' => $vote->user->name,
            'uid' => $vote->user->uuid,
            'votes' => $vote->votes,
        ]);

        return response()->json([
            'users' => $users,
            'sites' => $sites->count(),
            'top_votes' => $topVotes,
        ]);
    }

    private function getVotesCount(User $user)
    {
        return Vote::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }
}
