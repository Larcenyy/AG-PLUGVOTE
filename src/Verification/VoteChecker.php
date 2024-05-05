<?php

namespace Azuriom\Plugin\Vote\Verification;

use Azuriom\Models\User;
use Azuriom\Plugin\Vote\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VoteChecker
{
    /**
     * The votes sites supporting verification.
     *
     * @var array<string, VoteVerifier>
     */
    private array $sites = [];

    public function __construct()
    {
        $this->register(VoteVerifier::for('serveurliste.com')
            ->setApiUrl('https://serveurliste.com/api/vote?ip_address={ip}&api_token={server}')
            ->requireKey('api_key')
            ->verifyByJson('data.voted', true));

        $this->register(VoteVerifier::for('serveur-minecraft-vote.fr')
            ->setApiUrl('https://serveur-minecraft-vote.fr/api/v1/servers/{server}/vote/{ip}')
            ->retrieveKeyByRegex('/^serveur-minecraft-vote\.fr\/serveurs\/[\w-]+\.(\d+).*/')
            ->verifyByJson('canVote', false));

        $this->register(VoteVerifier::for('liste-serv-minecraft.fr')
            ->setApiUrl('https://liste-serv-minecraft.fr/api/check?server={server}&ip={ip}')
            ->retrieveKeyByRegex('/^liste-serv-minecraft\.fr\/serveur\?id=(\d+)/')
            ->verifyByJson('status', '200'));

        $this->register(VoteVerifier::for('serveurs-minecraft.org')
            ->setApiUrl('https://www.serveurs-minecraft.org/api/is_valid_vote.php?id={server}&ip={ip}&duration=5&format=json')
            ->retrieveKeyByRegex('/^serveurs-minecraft\.org\/vote\.php\?id=(\d+)/')
            ->verifyByJson('votes', '1'));

        $this->register(VoteVerifier::for('serveurs-mc.net')
            ->setApiUrl('https://serveurs-mc.net/api/hasVote/{server}/{ip}/10')
            ->retrieveKeyByRegex('/^serveurs-mc\.net\/serveur\/(\d+)/')
            ->verifyByJson('hasVote', true));

        $this->register(VoteVerifier::for('serveur-minecraft.fr')
            ->setApiUrl('https://serveur-minecraft.fr/api-{server}_{ip}.json')
            ->retrieveKeyByRegex('/^serveur-minecraft\.fr\/[\w-]+\.(\d+)/')
            ->verifyByJson('status', 'Success'));

        $this->register(VoteVerifier::for('serveursminecraft.org')
            ->setApiUrl('https://www.serveursminecraft.org/sm_api/peutVoter.php?id={server}&ip={ip}')
            ->retrieveKeyByRegex('/^serveursminecraft\.org\/serveur\/(\d+)/')
            ->verifyByDifferentValue('true'));

        $this->register(VoteVerifier::for('serveurs-minecraft.com')
            ->setApiUrl('https://serveurs-minecraft.com/api.php?Classement={server}&ip={ip}')
            ->retrieveKeyByRegex('/^serveurs-minecraft\.com\/serveur-minecraft\.php\?Classement=([^\/]+)/')
            ->verifyByJson('lastVote.date', function ($lastVoteTime, $json) {
                if (! $lastVoteTime) {
                    return false;
                }

                $now = Carbon::parse(Arr::get($json, 'reqVote.date')) ?? now();

                return Carbon::parse($lastVoteTime)->addMinutes(5)->isAfter($now) ? $lastVoteTime : false;
            }));

        $this->register(VoteVerifier::for('liste-serveurs.fr')
            ->setApiUrl('https://www.liste-serveurs.fr/api/checkVote/{server}/{ip}')
            ->retrieveKeyByRegex('/^liste-serveurs\.fr\/[\w-]+\.(\d+)/')
            ->verifyByJson('success', true));

        $this->register(VoteVerifier::for('serveur-top.fr')
            ->setApiUrl('https://serveur-top.fr/api/checkVote/{server}/{ip}')
            ->retrieveKeyByRegex('/^serveur-top\.fr\/[\w-]+\.(\d+)/')
            ->verifyByJson('success', true));

        $this->register(VoteVerifier::for('liste-minecraft-serveurs.com')
            ->setApiUrl('https://www.liste-minecraft-serveurs.com/Api/Worker/id_server/{server}/ip/{ip}')
            ->retrieveKeyByRegex('/^liste-minecraft-serveurs\.com\/Serveur\/(\d+)/')
            ->verifyByJson('result', 202));

        $this->register(VoteVerifier::for('minecraft-italia.net')
            ->setApiUrl('https://minecraft-italia.net/lista/api/vote/server?serverId={server}')
            ->requireKey('server_id')
            ->verifyByCallback(function (Response $response, User $user) {
                return collect($response->json())->contains('username', $user->name);
            }));

        $this->register(VoteVerifier::for('minecraft-server.eu')
            ->setApiUrl('https://minecraft-server.eu/api/v1/?object=votes&element=claim&key={server}&username={name}')
            ->requireKey('api_key')
            ->verifyByValue(1));

        $this->register(VoteVerifier::for('minecraft-mp.com')
            ->setApiUrl('https://minecraft-mp.com/api/?object=votes&element=claim&key={server}&username={name}')
            ->requireKey('api_key')
            ->verifyByValue(1));

        $this->register(VoteVerifier::for('minecraftpocket-servers.com')
            ->setApiUrl('https://minecraftpocket-servers.com/api/?object=votes&element=claim&key={server}&username={name}')
            ->requireKey('api_key')
            ->verifyByValue(1));

        $listForge = [
            'gmod-servers.com',
            'ark-servers.net',
            'rust-servers.net',
            'tf2-servers.com',
            'counter-strike-servers.net',
        ];

        foreach ($listForge as $domain) {
            $this->register(VoteVerifier::for($domain)
                ->setApiUrl("https://{$domain}/api/?object=votes&element=claim&key={server}&steamid={id}")
                ->requireKey('api_key')
                ->verifyByValue(1));
        }

        $this->register(VoteVerifier::for('trackyserver.com')
            ->setApiUrl('http://www.api.trackyserver.com/vote/?action=claim&key={server}&steamid={id}')
            ->requireKey('api_key')
            ->verifyByValue(1));

        $this->register(VoteVerifier::for('topminecraft.click')
            ->setApiUrl('https://topminecraft.click/api/vote/v1/{server}/{ip}')
            ->requireKey('api_key')
            ->verifyByJson('has_vote', true));

        $this->register(VoteVerifier::for('topminecraft.io')
            ->setApiUrl('https://topminecraft.io/api/vote/{server}/{ip}')
            ->requireKey('api_key')
            ->verifyByJson('status', 'success'));

        $this->register(VoteVerifier::for('serveur-prive.net')
            ->setApiUrl('https://serveur-prive.net/api/v1/servers/{server}/votes/{ip}')
            ->requireKey('api_key')
            ->verifyByJson('success', true));

        $this->register(VoteVerifier::for('top-serveurs.net')
            ->setApiUrl('https://api.top-serveurs.net/v1/votes/check-ip?server_token={server}&ip={ip}')
            ->requireKey('token')
            ->verifyByJson('code', 200));

        $this->register(VoteVerifier::for('top-games.net')
            ->setApiUrl('https://api.top-games.net/v1/votes/check-ip?server_token={server}&ip={ip}')
            ->requireKey('token')
            ->verifyByJson('code', 200));

        $this->register(VoteVerifier::for('liste-serveurs-minecraft.org')
            ->setApiUrl('https://api.liste-serveurs-minecraft.org/vote/vote_verification.php?server_id={server}&ip={ip}&duration=5')
            ->requireKey('server_id')
            ->verifyByValue('1'));

        $this->register(VoteVerifier::for('minecraft-server.net')
            ->requireKey('api_key')
            ->verifyByCallback(function (User $user, Site $site) {
                $response = Http::post('https://minecraft-server.net/api/', [
                    'action' => 'checkVote',
                    'key' => $site->verification_key,
                    'player' => $user->name,
                ]);

                return $response->body() === '1';
            }));

        $this->register(VoteVerifier::for('playbase.pro')
            ->setApiUrl('https://playbase.pro/api/vote/{server}/{ip}')
            ->retrieveKeyByRegex('/\/(\d+)-/')
            ->transformRequest(function (PendingRequest $request, User $user, Site $site) {
                return $request->withToken($site->verification_key);
            })
            ->verifyByJson('date', true));

        $this->register(VoteVerifier::for('mctop.su')
            ->requireKey('secret')
            ->verifyByPingback(function (Request $request, Site $site) {
                $name = $request->input('nickname');
                $token = $request->input('token');

                if ($token !== md5($name.$site->verification_key)) {
                    return null;
                }

                return User::where('name', $name)->value('id');
            }));

        $this->register(VoteVerifier::for('topcraft.club')
            ->requireKey('secret')
            ->verifyByPingback(function (Request $request, Site $site) {
                $name = $request->input('username');
                $timestamp = $request->input('timestamp');
                $signature = $request->input('signature');

                if ($signature !== sha1($name.$timestamp.$site->verification_key)) {
                    return null;
                }

                return User::where('name', $name)->value('id');
            }));

        $this->register(VoteVerifier::for('minecraftrating.ru')
            ->requireKey('secret')
            ->verifyByPingback(function (Request $request, Site $site) {
                $name = $request->input('username');
                $timestamp = $request->input('timestamp');
                $signature = $request->input('signature');

                if ($signature !== sha1($name.$timestamp.$site->verification_key)) {
                    return null;
                }

                return User::where('name', $name)->value('id');
            }));

        $this->register(VoteVerifier::for('gtop100.com')
            ->requireKey('api_key')
            ->verifyByPingback(function (Request $request, Site $site) {
                $key = $request->input('pingbackkey');

                if ($key !== $site->verification_key || $request->input('Successful') !== '0') {
                    return null;
                }

                return $request->input('VoterIP');
            }));
    }

    public function hasVerificationForSite(string $domain): bool
    {
        return array_key_exists($domain, $this->sites);
    }

    /**
     * Get the vote verifier for the given domain.
     */
    public function getVerificationForSite(string $domain): ?VoteVerifier
    {
        return $this->sites[$domain] ?? null;
    }

    /**
     * Try to verify if the user voted if the website is supported.
     * In case of failure or unsupported website true is returned.
     */
    public function verifyVote(Site $site, User $user, string $requestIp): bool
    {
        $host = $this->parseHostFromUrl($site->url);

        if ($host === null) {
            return true;
        }

        $verification = $this->getVerificationForSite($host);

        if ($verification === null) {
            return true;
        }

        return $verification->verifyVote($site, $user, $requestIp);
    }

    protected function register(VoteVerifier $verifier): void
    {
        $this->sites[$verifier->getSiteDomain()] = $verifier;
    }

    public function parseHostFromUrl(string $rawUrl): ?string
    {
        $url = parse_url($rawUrl);

        if ($url === false || ! array_key_exists('host', $url)) {
            return null;
        }

        $host = $url['host'];

        if (Str::startsWith($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }

    /**
     * Returns the list of registered sites.
     *
     * @return array<string, VoteVerifier>
     */
    public function getSites(): array
    {
        return $this->sites;
    }
}
