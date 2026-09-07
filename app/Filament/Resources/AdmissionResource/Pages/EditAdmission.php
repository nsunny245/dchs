<?php

namespace App\Filament\Resources\AdmissionResource\Pages;

use App\Actions\FinalizeAdmissionAction;
use App\Filament\Resources\AdmissionResource;
use App\Models\StudentFeeAccount;
use App\Services\Admissions\AdmissionDraftService;
use App\Services\Fees\AdmissionVoucherReconciliationService;
use App\Services\Fees\ConcessionCalculator;
use App\Services\Fees\InstallmentPlanGenerator;
use App\Services\Fees\OfficialFeePlanData;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditAdmission extends EditRecord
{
    protected static string $resource = AdmissionResource::class;

    protected static string $view = 'filament.resources.admission-resource.pages.edit-admission';

    public function getSubheading(): ?string
    {
        return 'Review the seven-step record, save a draft, or finalize the student and financial documents.';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $savedRows = collect($data['custom_installments'] ?? [])->values();
        $legacyAdmissionRow = $savedRows->first(function (array $row): bool {
            return str_contains(strtolower((string) ($row['title'] ?? '')), 'admission');
        });

        if ((float) ($data['custom_admission_fee'] ?? 0) <= 0 && $legacyAdmissionRow) {
            $data['custom_admission_fee'] = (float) ($legacyAdmissionRow['amount'] ?? 0);
            $savedRows = $savedRows
                ->reject(fn (array $row): bool => $row === $legacyAdmissionRow)
                ->values();
            $data['custom_installment_count'] = max(1, $savedRows->count());
            $data['custom_installment_start_date'] = data_get($savedRows->first(), 'due_date')
                ?: $data['admission_date']
                ?: now()->toDateString();
            $data['custom_installments'] = AdmissionResource::buildInstallmentRows(
                $data['custom_installment_count'],
                max(
                    0,
                    (float) ($data['custom_tuition_fee'] ?? 0)
                        - (float) ($data['concession_amount'] ?? 0)
                        - (float) $data['custom_admission_fee'],
                ),
                $data['custom_installment_start_date'],
                (int) ($data['custom_installment_interval_months'] ?? 1),
            );
            $savedRows = collect($data['custom_installments']);
        }

        if (blank($data['custom_installment_start_date'] ?? null)) {
            $data['custom_installment_start_date'] = data_get($savedRows->first(), 'due_date')
                ?: $data['admission_date']
                ?: now()->toDateString();
        }

        if (
            empty($data['custom_installments'])
            && (int) ($data['custom_installment_count'] ?? 0) > 0
            && (float) ($data['custom_tuition_fee'] ?? 0) > 0
        ) {
            $data['custom_installments'] = AdmissionResource::buildInstallmentRows(
                (int) $data['custom_installment_count'],
                max(0, (float) $data['custom_tuition_fee'] - (float) ($data['concession_amount'] ?? 0) - (float) ($data['custom_admission_fee'] ?? 0)),
                $data['custom_installment_start_date'] ?? $data['admission_date'] ?? now(),
                (int) ($data['custom_installment_interval_months'] ?? 1),
            );
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('saveDraft')
                ->label('Save Draft')
                ->icon('heroicon-o-bookmark')
                ->action(function () {
                    app(AdmissionDraftService::class)->save(
                        $this->form->getRawState(),
                        filament()->auth()->user(),
                    );
                    Notification::make()->success()->title('Admission draft saved')->send();
                })
                ->color('gray'),
            Actions\Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
            Actions\Action::make('finalize')
                ->label('Submit Admission & Generate Documents')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        $this->save(shouldRedirect: false);
                        app(FinalizeAdmissionAction::class)
                            ->execute($this->record, filament()->auth()->id());
                        $this->redirect(route('admissions.complete', $this->record));
                    } catch (\Throwable $exception) {
                        $reference = Str::upper(Str::random(8));
                        report($exception);
                        logger()->error('Saved admission could not be finalized.', [
                            'reference' => $reference,
                            'admission_id' => $this->record->id,
                            'actor_id' => filament()->auth()->id(),
                            'exception' => $exception::class,
                            'message' => $exception->getMessage(),
                        ]);
                        $message = $exception instanceof ValidationException
                            ? collect($exception->errors())->flatten()->first()
                            : "Enrollment could not finish. Error reference: {$reference}.";
                        Notification::make()->danger()->persistent()
                            ->title('Enrollment could not be completed')
                            ->body($message)
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! filament()->auth()->user()->hasRole('Super Admin')) {
            $official = app(OfficialFeePlanData::class)->forAdmission($data);
            foreach (['custom_tuition_fee', 'custom_enrollment_fee', 'custom_verification_fee', 'custom_examination_fee', 'custom_other_misc'] as $field) {
                $data[$field] = $official[$field];
            }
            $data['concession_status'] = $this->record->concession_status;
        }

        if (($data['concession_type'] ?? 'none') !== 'none') {
            $money = app(InstallmentPlanGenerator::class);
            $packagePaisa = $money->toPaisa($data['custom_tuition_fee'] ?? 0);
            $concessionPaisa = app(ConcessionCalculator::class)->calculate(
                number_format($packagePaisa / 100, 2, '.', ''),
                $data['concession_value_type'] ?? 'fixed',
                $data['concession_value'] ?? $data['concession_amount'] ?? 0,
            );
            $data['concession_amount'] = number_format($concessionPaisa / 100, 2, '.', '');

            if (($data['concession_status'] ?? null) === 'approved' && ! $this->record->concession_approved_at) {
                $data['concession_approved_by'] = filament()->auth()->id();
                $data['concession_approved_at'] = now();
            }
        }

        $installmentCount = max(1, min(12, (int) ($data['custom_installment_count'] ?? 1)));
        $remainingTuition = max(
            0,
            (float) ($data['custom_tuition_fee'] ?? 0)
                - (float) ($data['concession_amount'] ?? 0)
                - (float) ($data['custom_admission_fee'] ?? 0),
        );
        $data['custom_installment_count'] = $installmentCount;
        $data['custom_installments'] = AdmissionResource::buildInstallmentRows(
            $installmentCount,
            $remainingTuition,
            $data['custom_installment_start_date'] ?? $data['admission_date'] ?? now(),
            (int) ($data['custom_installment_interval_months'] ?? 1),
        );

        return $data;
    }

    protected function afterSave(): void
    {
        $account = StudentFeeAccount::query()
            ->where('admission_id', $this->record->id)
            ->whereHas('student')
            ->first();

        if (! $account) {
            return;
        }

        try {
            app(AdmissionVoucherReconciliationService::class)->reconcile(
                $account,
                filament()->auth()->id(),
            );
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Admission saved; fee schedule needs review')
                ->body(collect($exception->errors())->flatten()->first() ?: $exception->getMessage())
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
