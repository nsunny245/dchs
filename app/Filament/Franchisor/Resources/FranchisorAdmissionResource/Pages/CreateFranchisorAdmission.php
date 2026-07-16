<?php

namespace App\Filament\Franchisor\Resources\FranchisorAdmissionResource\Pages;

use App\Filament\Franchisor\Resources\FranchisorAdmissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFranchisorAdmission extends CreateRecord
{
    protected static string $resource = FranchisorAdmissionResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $seatCost = $this->data['seat_cost'] ?? 0;
        $installments = $this->data['installments'] ?? [];

        if ($seatCost > 0) {
            $payment = \App\Models\FranchisorStudentPayment::create([
                'franchisor_id' => $record->franchisor_id,
                'admission_id' => $record->id,
                'total_amount' => $seatCost,
                'paid_amount' => 0.00,
                'status' => 'unpaid',
            ]);

            foreach ($installments as $inst) {
                $payment->installments()->create([
                    'title' => $inst['title'],
                    'amount' => $inst['amount'],
                    'due_date' => $inst['due_date'] ?? null,
                    'status' => 'unpaid',
                ]);
            }
        }
    }
}
