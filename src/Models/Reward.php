<?php

namespace Azuriom\Plugin\Vote\Models;

use Azuriom\Models\Role;
use Azuriom\Models\Server;
use Azuriom\Models\Traits\HasImage;
use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\Loggable;
use Azuriom\Models\Traits\Searchable;
use Azuriom\Models\User;
use Azuriom\Plugin\Loyalty\Models\Gift as LoyaltyGift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;


/**
 * @property int $id
 * @property string $name
 * @property string $image
 * @property string $image_bonus
 * @property float $chances
 * @property int|null $money
 * @property int|null $money_bonus
 * @property bool $need_online
 * @property string[] $commands
 * @property string[] $commands_bonus
 * @property string[] $roles_authorized
 * @property int[] $monthly_rewards
 * @property bool $is_enabled
 * @property bool $double_accept
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Vote\Models\Vote[] $votes
 * @property \Illuminate\Support\Collection|\Azuriom\Models\Server[] $servers
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 */
class Reward extends Model
{
    use HasImage;
    use HasTablePrefix;
    use Loggable;
    use Searchable;

    /**
     * The table prefix associated with the model.
     */
    protected string $prefix = 'vote_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'image', 'image_bonus', 'chances', 'money', 'money_bonus', 'commands', 'commands_bonus', 'roles_authorized', 'monthly_rewards', 'need_online', 'is_enabled', 'double_accept',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'commands' => 'array',
        'roles_authorized' => 'array',
        'commands_bonus' => 'array',
        'monthly_rewards' => 'array',
        'is_enabled' => 'boolean',
        'double_accept' => 'boolean',
    ];

    /**
     * The attributes that can be used for search.
     *
     * @var array<int, string>
     */
    protected array $searchable = [
        'name',
    ];

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'vote_reward_site');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class, 'vote_reward_server');
    }


    public function getNameBonus(): string{
        $commands_bonus = $this->commands_bonus ?? [];

        foreach ($commands_bonus as $bonus) {
            $data = explode(',', $bonus);

            return $data[2];
        }

        return '';
    }

    public function dispatch(User|Vote $target): void
    {
        $user = $target instanceof User ? $target : $target->user;
        $siteName = $target instanceof Vote ? $target->site->name : '?';

        if($this->money_bonus > 0){
            $authorizedRoleIds = $this->roles_authorized;

            $hasAuthorizedRole = in_array($user->role_id, $authorizedRoleIds);
            if ($hasAuthorizedRole) {
                $user->addMoney($this->money_bonus);
            }
        }

        if ($this->money > 0) {
            $user->addMoney($this->money);

            // Si la récompense est possible en doublé et que le système d'évènement double est actif
            if(setting('vote.active_date')) {
                $currentDate = date('Y-m-d');
                $voteEndDate = setting('vote.whoEnd');
                $voteStartDate = setting('vote.whoStart');

                // Vérifier si la date de fin n'est pas dépassée
                if ($currentDate <= $voteEndDate) {
                    // Vérifier si la date de début est atteinte ou passée
                    if ($currentDate >= $voteStartDate) {
                        // Récompense doublée
                        if($this->double_accept) {
                            $user->addMoney($this->money);
                        }
                    }
                }
            }

            if(setting('vote.maxDays') > 0) {
                $currentDate = date('Y-m-d');
                $startMonth = date('Y-m-01', strtotime($currentDate));
                $endMonth = date('Y-m-' . sprintf('%02d', setting('vote.maxDays')), strtotime($currentDate));

                // Si la date d'aujourd'hui ce situe entre le 01 et le maxDays c'est ok
                if ($currentDate >= $startMonth && $currentDate <= $endMonth) {
                    if($this->double_accept) {
                        $user->addMoney($this->money);
                    }
                }
            }
        }

        $commands = $this->commands ?? [];
        $commands_bonus = $this->commands_bonus ?? [];

        if ($globalCommands = setting('vote.commands')) {
            $commands = array_merge($commands, json_decode($globalCommands));
        }

        if (empty($commands)) {
            return;
        }

        $commands = array_map(fn (string $command) => str_replace([
            '{reward}', '{site}',
        ], [$this->name, $siteName], $command), $commands);

        foreach ($commands as $command) {
            // Séparation des données
            $data = explode(',', $command);

            if (count($data) >= 3) { // Vérifie la longueur du tableau
                $idGIFT = $data[0];
                $amountGIFT = $data[1];
                $nameGIFT = $data[2];

                if ($amountGIFT == 0) {
                    $amountGIFT = 1;
                }

                if ($nameGIFT == '') {
                    $nameGIFT = "Aucun nom";
                }

                LoyaltyGift::create([
                    'item_id' => $idGIFT,
                    'amount' => $amountGIFT,
                    'name' => $nameGIFT,
                    'account' => $user->name
                ]);
            }
        }

        if (!empty($commands_bonus)) {
            // Récupérer les identifiants des rôles autorisés
            $authorizedRoleIds = $this->roles_authorized;

            // Récupère le rôle de l'user
            $userRoleId = $user->role_id;

            // Vérifier si l'utilisateur possède au moins l'un des rôles autorisés
            $hasAuthorizedRole = in_array($userRoleId, $authorizedRoleIds);

            if ($hasAuthorizedRole) {
                // Si l'utilisateur a au moins l'un des rôles autorisés, attribuer le bonus
                foreach ($commands_bonus as $bonus) {
                    $data = explode(',', $bonus);

                    if (count($data) >= 3) { // Vérifie la longueur du tableau
                        $idGIFT = $data[0];
                        $amountGIFT = $data[1];
                        $nameGIFT = $data[2];

                        if ($amountGIFT == 0) {
                            $amountGIFT = 1;
                        }

                        if ($nameGIFT == '') {
                            $nameGIFT = "Aucun nom";
                        }

                        LoyaltyGift::create([
                            'item_id' => $idGIFT,
                            'amount' => $amountGIFT,
                            'name' => $nameGIFT,
                            'account' => $user->name
                        ]);
                    }
                }
            }
        }


        foreach ($this->servers as $server) {
            $server->bridge()->sendCommands($commands, $user, $this->need_online);
        }
    }

    /**
     * Stocke l'image avec un nom de fichier unique.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  bool  $save
     * @return string
     */
    public function storeImage_Bonus(UploadedFile $file, bool $save = false): string
    {
        $this->deleteImage();

        $path = basename($file->storePublicly($this->resolveImagePath(), $this->imageDisk));

        $this->setAttribute($this->getImageKey(), $path);

        if ($save) {
            $this->save();
        }

        return $this->imageUrl();
    }

    /**
     * Scope a query to only include enabled vote rewards.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('is_enabled', true);
        $query->where('double_accept', true);
    }
}
