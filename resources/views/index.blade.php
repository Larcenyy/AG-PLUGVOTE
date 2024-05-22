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
            <nav role="pagination">
                <ul class="pagination-wrapper pagination list-unstyled d-flex justify-content-center align-items-center gap-4">
                    <div class="spinner-border pagination-spinner" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </ul>
            </nav>
        </div>
    </div>
    @if($displayRewards)
        <div class="mt-4">
            <h2 class="card-title bg-black p-2">
                <i class="bi bi-gift-fill"></i> {{ trans('vote::messages.sections.rewards') }}
            </h2>
            <div class="container d-flex flex-wrap gap-3 justify-content-center align-items-center mt-5 mb-3">
                <div id="carouselExampleControls" class="carousel slide overflow-hidden" data-bs-ride="carousel">
                    <div class="carousel-inner d-flex" style="gap: 20px!important">
                        @foreach($rewards as $index => $reward)
                            <div class="carousel-item @if($index == 0) active @endif" data-bs-interval="10000" style="width: 60%!important;flex: 0 0 20%;!important;">
                                <div class="d-flex gap-1 justify-content-center">
                                    <div class="flip-card rounded @if($reward->commands_bonus || $reward->rewardGiveMoney_Bonus()) border-warning border-2 @endif">
                                        <div class="flip-card-inner @if($reward->commands_bonus || $reward->rewardGiveMoney_Bonus()) flip @endif">
                                            {{-- FACE NORMAL --}}
                                            <div class="flip-card-front d-flex flex-column justify-content-between rounded">
                                                <div>
                                                    <h5 class="bg-dark bg-opacity-50 p-2 mt-3">{{ $reward->name }}</h5>
                                                </div>

                                                <div class="row justify-content-center px-3">
                                                    @if($reward->commands || $reward->commands_bonus)
                                                        <div class="col-md-6 text-center @if($reward->commands && $reward->rewardGiveMoney()) border-end border-dark @endif">
                                                            <img src="{{ $reward->imageUrl() }}" width="150">
                                                        </div>
                                                    @endif
                                                    @if($reward->rewardGiveMoney())
                                                        <div class="col-md-6 text-center">
                                                            <img src="{{ plugin_asset('vote', 'img/coin.png') }}" alt="Ogrine" width="150">
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="flip-card-footer bg-dark bg-opacity-50 p-2 d-flex flex-column align-items-center">
                                                    <span class="">{{ $reward->chances }}%</span>
                                                    <span>{{ $reward->getNameSite($reward->id) }}</span> <!-- Appel de getNameSite() pour obtenir le nom du site -->
                                                </div>
                                            </div>

                                            {{-- FACE BONUS --}}
                                            <div class="flip-card-back d-flex flex-column justify-content-between">
                                                <div class="position-relative">
                                                    <div class="position-absolute top-0 end-0 text-warning fs-2" style="top: -5px !important;"><i class="bi bi-bookmark-star-fill"></i></div>
                                                    <h5 class="bg-dark bg-opacity-50 p-2 mt-3">{{ $reward->getNameBonus() }} @if($reward->rewardGiveMoney_Bonus()) +
                                                        {{intval($reward->money_bonus)}} {{money_name()}} @endif</h5>
                                                </div>

                                                @if($reward->rewardGiveMoney_Bonus() && $reward->commands_bonus)
                                                    <div class="row justify-content-center px-3">
                                                @endif
                                                    @if($reward->image_bonus)
                                                        <div class="col-md-6 text-center @if($reward->rewardGiveMoney_Bonus() && $reward->commands) border-end border-dark border-2 @endif">
                                                            <img src="{{ $reward->imageUrl_Bonus() }}" alt="{{ $reward->getNameBonus() }}" width="150">
                                                        </div>
                                                    @endif
                                                    @if($reward->rewardGiveMoney_Bonus())
                                                        <div class="col-md-6 text-center">
                                                            <img src="{{ plugin_asset('vote', 'img/coin.png') }}" alt="Ogrine" width="150">
                                                        </div>
                                                    @endif
                                                @if($reward->rewardGiveMoney_Bonus() && $reward->commands_bonus)
                                                    </div>
                                                @endif

                                                @if(!in_array($user->role_id, $reward->roles_authorized))
                                                    <a href="{{ route('shop.home') }}" class="btn btn-primary rounded-0 w-100 p-2">Débloquer la récompense</a>
                                                @else
                                                    <h5 class="p-2 mt-3 mb-0 text-uppercase text-warning fs-6">Récompense bonus</h5>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
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
            window.username = '{{ $user->name }}';

            let currentTranslateX = 0; // Variable pour stocker la valeur actuelle de la translation

            function showSlide() {
                document.querySelector('.carousel-inner').style.transform = `translateX(${currentTranslateX}px)`;
            }

            function nextSlide() {
                currentTranslateX -= 50; // Décrémenter de 50px à chaque clic sur "suivant"
                showSlide();
            }

            function prevSlide() {
                currentTranslateX += 50; // Incrémenter de 50px à chaque clic sur "précédent"
                showSlide();
            }


            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('.carousel-control-next').addEventListener('click', nextSlide);
                document.querySelector('.carousel-control-prev').addEventListener('click', prevSlide);
            });
        </script>
    @endauth
@endpush

@push('styles')
    <style>
        .carousel-item {
            transition: transform 0.6s ease;
        }

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

{{--{{dd($votes->all())}}--}}
@include('vote::elements.script')

