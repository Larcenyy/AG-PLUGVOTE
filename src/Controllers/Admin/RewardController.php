<?php

namespace Azuriom\Plugin\Vote\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Role;
use Azuriom\Models\Server;
use Azuriom\Plugin\Vote\Models\Reward;
use Azuriom\Plugin\Vote\Requests\RewardRequest;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('vote::admin.rewards.index', [
            'rewards' => Reward::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vote::admin.rewards.create', [
            'servers' => Server::executable()->get(),
            'cron' => function_exists('scheduler_running') && scheduler_running(),
            'roles' => Role::orderByDesc('power')->get(),
            'reward' => new Reward(), // Créer une instance de la récompense vide
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RewardRequest $request)
    {
        $reward = Reward::create($request->validated());

        $reward->servers()->sync($request->input('servers', []));

        if ($request->hasFile('image')) {
            $reward->storeImage($request->file('image'), true);
        }

        if ($request->hasFile('image_bonus')) {
            $reward->storeImage($request->file('image_bonus'), true);
        }

        return to_route('vote.admin.rewards.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reward $reward)
    {
        return view('vote::admin.rewards.edit', [
            'reward' => $reward->load('servers'),
            'servers' => Server::executable()->get(),
            'cron' => function_exists('scheduler_running') && scheduler_running(),
            'roles' => Role::orderByDesc('power')->get(),
            'roles_authorized' => collect($reward->roles_authorized),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RewardRequest $request, Reward $reward)
    {
        $reward->update($request->validated());

        $reward->servers()->sync($request->input('servers', []));

        if ($request->hasFile('image')) {
            $reward->storeImage($request->file('image'), true);
        }

        if ($request->hasFile('image_bonus')) {
            $reward->storeImage_Bonus($request->file('image_bonus'), true);
        }


        return to_route('vote.admin.rewards.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Reward $reward)
    {
        $reward->delete();

        return to_route('vote.admin.rewards.index')
            ->with('success', trans('messages.status.success'));
    }
}
