@extends('admin.layouts.admin')

@section('title', trans('vote::admin.settings.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-body">

            <form action="{{ route('vote.admin.settings') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="topPlayersCount">{{ trans('vote::admin.settings.count') }}</label>
                    <input type="number" class="form-control" id="topPlayersCount" name="top-players-count" min="5" max="100" value="{{ $topPlayersCount }}" required="required">
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="displayRewards" name="display-rewards" @checked(display_rewards())>
                        <label class="form-check-label" for="displayRewards">{{ trans('vote::admin.settings.display-rewards') }}</label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="ipCompatibility" name="ip_compatibility" @if($ipCompatibility) checked @endif aria-describedby="ipCompatibilityLabel">
                        <label class="form-check-label" for="ipCompatibility">{{ trans('vote::admin.settings.ip_compatibility') }}</label>
                    </div>
                    <small id="ipCompatibilityLabel" class="form-text">{{ trans('vote::admin.settings.ip_compatibility_info') }}</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ trans('vote::admin.settings.commands') }}</label>

                    @include('admin.elements.list-input', ['name' => 'commands', 'values' => $commands])

                    <small class="form-text">@lang('vote::admin.rewards.commands')</small>
                </div>

                <hr>
                <div class="mb-3 form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="enableSwitch_Accept" name="active_date" @checked(old('active_date', $active_date ?? false))>
                    <label class="form-check-label" for="enableSwitch">Activité un évènement de date double récompense</label>
                </div>

                <div class="mb-3">
                    <h2>1. Date prédifini</h2>
                    <div class="mb-3 d-flex flex-column gap-3">
                        <div class="mb-3 d-flex flex-column flex-wrap align-items-center">
                            <label class="form-label" for="whoStart">Date de début à choisir</label>
                            <input value="{{ setting('vote.whoStart') }}" type="datetime-local" class="form-control mb-2 w-50" id="whoStart" name="whoStart">
                        </div>
                    </div>
                    <div class="mb-3 d-flex flex-column gap-3">
                        <div class="mb-3 d-flex flex-column flex-wrap align-items-center">
                            <label class="form-label" for="whoEnd">Date de fin à choisir</label>
                            <input value="{{ setting('vote.whoEnd') }}" type="datetime-local" class="form-control mb-2 w-50" id="whoEnd" name="whoEnd">
                        </div>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h2>2. Début du mois</h2>
                    <div class="mb-3 d-flex flex-column gap-3">
                        <div class="mb-3 d-flex flex-column flex-wrap align-items-center">
                            <label class="form-label" for="maxDays">Jour maximum du début de mois</label>
                            <small>Exemple début du mois = 01/05, si vous définissez 5, tous les mois du 01/05 au 05/05 les récompenses seront doubler</small>
                            <input value="{{ setting('vote.maxDays', 5) }}" type="number" min="0" max="31" class="form-control mb-2 w-50" id="maxDays" name="maxDays">
                        </div>
                    </div>
                </div>


                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
                </button>

            </form>

        </div>
    </div>
@endsection
