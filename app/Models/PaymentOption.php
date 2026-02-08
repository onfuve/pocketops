<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{
    protected $fillable = [
        'label',
        'holder_name',
        'bank_name',
        'value',
        'type',
        'sort',
        'card_number',
        'iban',
        'account_number',
        'print_card_number',
        'print_iban',
        'print_account_number',
    ];

    protected $casts = [
        'print_card_number' => 'boolean',
        'print_iban' => 'boolean',
        'print_account_number' => 'boolean',
    ];

    /**
     * Lines to show on invoice print (label + value) for each enabled field.
     * @param array|null $override e.g. ['print_card_number' => true, 'print_iban' => true, 'print_account_number' => false]
     */
    public function linesForPrint(?array $override = null): array
    {
        $printCard = $override['print_card_number'] ?? $this->print_card_number;
        $printIban = $override['print_iban'] ?? $this->print_iban;
        $printAccount = $override['print_account_number'] ?? $this->print_account_number;

        if (! $printCard && ! $printIban && ! $printAccount) {
            return [];
        }

        $lines = [];
        if (($this->holder_name ?? '') !== '') {
            $lines[] = ['label' => 'صاحب حساب/کارت', 'value' => $this->holder_name];
        }
        if (($this->bank_name ?? '') !== '') {
            $lines[] = ['label' => 'بانک', 'value' => $this->bank_name];
        }
        if ($printCard && ($this->card_number ?? '') !== '') {
            $lines[] = ['label' => 'شماره کارت', 'value' => $this->card_number];
        }
        if ($printIban && ($this->iban ?? '') !== '') {
            $lines[] = ['label' => 'شبا', 'value' => $this->iban];
        }
        if ($printAccount && ($this->account_number ?? '') !== '') {
            $lines[] = ['label' => 'شماره حساب', 'value' => $this->account_number];
        }
        if (empty($lines) && ($this->value ?? '') !== '') {
            $lines[] = ['label' => $this->label, 'value' => $this->value];
        }
        return $lines;
    }

    public static function forPrint(): \Illuminate\Database\Eloquent\Collection
    {
        return static::orderBy('sort')->get();
    }

    /** Scope: only options that have at least one print flag enabled in settings (for invoice form). */
    public function scopePrintableInSettings($query)
    {
        return $query->where(function ($q) {
            $q->where('print_card_number', true)
                ->orWhere('print_iban', true)
                ->orWhere('print_account_number', true);
        });
    }
}
