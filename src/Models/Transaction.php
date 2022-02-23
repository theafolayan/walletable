<?php

namespace Walletable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Walletable\Actions\ActionManager;
use Walletable\Models\Traits\TransactionRelations;
use Walletable\Models\Traits\WorkWithData;
use Walletable\Money\Money;
use Walletable\Traits\ConditionalUuid;
use Walletable\WalletManager;

class Transaction extends Model
{
    use HasFactory;
    use ConditionalUuid;
    use TransactionRelations;
    use WorkWithData;

    public $timestamps = false;

    protected $transactionCache = [];

    public function getAmountAttribute()
    {
        return new Money(
            $this->getRawOriginal('amount'),
            $this->currency
        );
    }

    public function getActionAttribute()
    {
        if (isset($this->transactionCache['action'])) {
            return $this->transactionCache['action'];
        }

        return $this->transactionCache['action'] = new ActionManager(
            $this,
            App::make(WalletManager::class)
                ->action($this->getRawOriginal('action'))
        );
    }

    public function getTitleAttribute()
    {
        return $this->action->title();
    }

    public function getDriverAttribute()
    {
        return App::make(WalletManager::class)->driver($this->getRawOriginal('driver'));
    }

    public function getCurrencyAttribute()
    {
        return $this->driver->currency($this->getRawOriginal('currency'));
    }
}