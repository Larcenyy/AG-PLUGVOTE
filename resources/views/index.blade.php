@extends('layouts.app')

@section('title', trans('vote::messages.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('vote', 'css/css.css') }}">
@endpush

@section('content')
    <div class="container row p-0">
        <div class="mb-4 col-lg-4 h-100">
            <div class="bg-black p-4 text-center position-relative w-100" id="vote-card">
                <h1>{{ trans('vote::messages.sections.vote') }}</h1>
                <p>Cliquez pour voter !</p>
                <div class="spinner-parent h-100">
                    <div class="spinner-border text-white" role="status"></div>
                </div>

                <div class="@auth d-none @endauth" data-vote-step="1">
                    <form class="row justify-content-center" action="{{ route('vote.verify-user', '') }}" id="voteNameForm">
                        <div class="col-md-6 col-lg-4">
                            <div class="mb-3">
                                <input type="text" id="stepNameInput" name="name" class="form-control"
                                       value="{{ $name }}"
                                       placeholder="{{ trans('messages.fields.name') }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                {{ trans('messages.actions.continue') }}
                                <span class="d-none spinner-border spinner-border-sm load-spinner" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class=" @guest d-none @endguest d-flex h-100 flex-column gap-3" data-vote-step="2">
                    @forelse($sites as $site)
                        <a class="btn btn-primary" href="{{ $site->url }}" target="_blank" rel="noopener noreferrer"
                           data-vote-id="{{ $site->id }}"
                           data-vote-url="{{ route('vote.vote', $site) }}"
                           @auth data-vote-time="{{ $site->getNextVoteTime($user, $request)?->valueOf() }}" @endauth>
                            <span class="badge bg-secondary text-white vote-timer"></span> {{ $site->name }}
                        </a>
                    @empty
                        <div class="alert alert-warning" role="alert">
                            {{ trans('vote::messages.errors.site') }}
                        </div>
                    @endforelse
                </div>

                <div class="d-none" data-vote-step="3">
                    <p id="vote-result"></p>
                </div>
            </div>
        </div>

        <div class="bg-black p-2 col-lg-8 h-100">
            <h2 class="card-title text-center">
                {{ trans('vote::messages.sections.top') }}
            </h2>
            @auth
                <p class="mt-3 mb-0 text-center">{{ trans_choice('vote::messages.votes', $userVotes) }}</p>
            @endauth
            <table class="table mb-0 table-striped">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">{{ trans('messages.fields.name') }}</th>
                    <th scope="col">{{ trans('vote::messages.fields.votes') }}</th>
                </tr>
                </thead>
                <tbody id="voting">
{{--                    @foreach($votes as $id => $vote)--}}
{{--                        <tr>--}}
{{--                            <th scope="row">#{{ $id }}</th>--}}
{{--                            <td>{{ $vote->user->name }}</td>--}}
{{--                            <td>{{ $vote->votes }}</td>--}}
{{--                        </tr>--}}
{{--                    @endforeach--}}
                </tbody>
            </table>
            <button id="prevBtn">Précédent</button>
            <button id="nextBtn">Suivant</button>
        </div>
    </div>

    @if($displayRewards)
        <div class="mt-4">
            <h2 class="card-title bg-black p-2">
                <i class="bi bi-gift-fill"></i> {{ trans('vote::messages.sections.rewards') }}
            </h2>
            <div class="container d-flex flex-wrap gap-3 align-items-center mt-5 mb-3">
                <div id="carouselExampleControls" class="carousel">
                    <div class="carousel-inner">
                        @foreach($rewards as $reward)
                            <div class="carousel-item active">
                                <div class="card bg-black">
                                    <div class="mt-4">
                                        <h5 class="card-title bg-light-subtle p-2">{{ $reward->name }}</h5>
                                        <div class="img-wrapper"><img src="{{ $reward->imageUrl() }}" class="d-block" alt="{{ $reward->name }}" width="150px"></div>
                                        <p class="card-text">
                                            Some quick example text to build on the card title and make up the bulk of the
                                            card's content.
                                        </p>
                                        <a href="#" class="btn btn-primary">Go somewhere</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
{{--                        @foreach($rewards as $reward)--}}
{{--                            @if($reward->commands_bonus && $reward->commands)--}}
{{--                                <div style="cursor: pointer;min-height: 200px" data-bs-toggle="tooltip" data-bs-placement="top" title="%{{ $reward->chances }} de l'obtenir" class=" border-light border col-lg-3 d-flex flex-column justify-content-between bg-light-subtle text-center">--}}
{{--                                    <div class="p-2 z-1 d-flex flex-column gap-1 align-items-center">--}}
{{--                                        <small class="text-secondary">Récompense gratuite</small>--}}
{{--                                        <h5 class="d-flex align-items-center justify-content-center mb-0">--}}
{{--                                            {{ $reward->name }}--}}
{{--                                        </h5>--}}
{{--                                        @if($reward->image)--}}
{{--                                            <img src="{{ $reward->imageUrl() }}" alt="{{ $reward->name }}" width="50px">--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                    @if($reward->commands_bonus)--}}
{{--                                        <div style="cursor: @if(!in_array($user->role_id, $reward->roles_authorized)) pointer @else not-allowed @endif;" class="border-warning bg-warning-subtle border border-2 m-0">--}}
{{--                                            <div class="m-0 @if(!in_array($user->role_id, $reward->roles_authorized)) opacity-100 @else opacity-25 @endif">--}}
{{--                                                <small class="text-secondary">Récompense bonus</small>--}}
{{--                                                <p class="m-0">{{ $reward->getNameBonus() }}</p>--}}
{{--                                                @if($reward->money_bonus > 0)--}}
{{--                                                    <hr>--}}
{{--                                                    <small class="text-secondary">Bonus de {{ $reward->money_bonus }} {{ money_name() }}</small>--}}
{{--                                                @endif--}}
{{--                                            </div>--}}
{{--                                            @if(in_array($user->role_id, $reward->roles_authorized))--}}
{{--                                                <a href="{{ route('shop.home') }}" class="btn btn-primary rounded-0 w-100 p-1">Débloquer la récompense</a>--}}
{{--                                            @endif--}}
{{--                                        </div>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            @elseif($reward->commands_bonus && !$reward->commands)--}}
{{--                                <div style="cursor: @if(in_array($user->role_id, $reward->roles_authorized)) pointer @else not-allowed @endif; min-height: 200px" data-bs-toggle="tooltip" data-bs-placement="top" title="%{{ $reward->chances }} de l'obtenir" class=" border-warning bg-warning-subtle border col-lg-3 d-flex flex-column justify-content-between text-center">--}}
{{--                                    <div class="p-2 z-1 d-flex flex-column gap-2 align-items-center @if(in_array($user->role_id, $reward->roles_authorized)) opacity-100 @else opacity-25 @endif">--}}
{{--                                        <small class="text-secondary">Récompense VIP</small>--}}
{{--                                        <h5 class="d-flex align-items-center justify-content-center mb-0">--}}
{{--                                            {{ $reward->getNameBonus() }}--}}
{{--                                        </h5>--}}
{{--                                        @if($reward->money_bonus > 0)--}}
{{--                                            <h5>+ {{ intval($reward->money_bonus) }} {{ money_name() }}</h5>--}}
{{--                                        @endif--}}
{{--                                        @if($reward->image)--}}
{{--                                            <img src="{{ $reward->imageUrl() }}" alt="{{ $reward->name }}" width="50px">--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                    @if(!in_array($user->role_id, $reward->roles_authorized))--}}
{{--                                        <a href="{{ route('shop.home') }}" class="btn btn-primary rounded-0">Débloquer la récompense</a>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            @else--}}
{{--                                <div style="cursor: pointer;min-height: 200px" data-bs-toggle="tooltip" data-bs-placement="top" title="%{{ $reward->chances }} de l'obtenir" class="border-light border col-lg-3 d-flex flex-column justify-content-between bg-light-subtle text-center">--}}
{{--                                    <div class="p-2 z-1 d-flex flex-column gap-2 align-items-center h-100">--}}
{{--                                        <small class="text-secondary">Récompense gratuite</small>--}}
{{--                                        <h5 class="d-flex align-items-center justify-content-center mb-0">--}}
{{--                                            {{ $reward->name }}--}}
{{--                                        </h5>--}}
{{--                                        @if($reward->image)--}}
{{--                                            <img src="{{ $reward->imageUrl() }}" alt="{{ $reward->name }}" width="100px">--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                        @endforeach --}}
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection



@push('scripts')
    @if($ipv6compatibility)
        <script src="https://ipv6-adapter.com/api/v1/api.js" async defer></script>
    @endif

    <script src="{{ plugin_asset('vote', 'js/vote.js') }}" defer></script>
    @auth
        <script>
            window.username  = '{{ $user->name }}';
        </script>
    @endauth
@endpush

@push('styles')
    <style>
        #vote-card .spinner-parent {
            display: none;
        }

        #vote-card.voting .spinner-parent {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(70, 70, 70, 0.6);
            z-index: 10;
        }
    </style>
@endpush

@include('vote::elements.script')

