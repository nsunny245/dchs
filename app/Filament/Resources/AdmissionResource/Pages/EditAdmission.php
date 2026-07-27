<?php

namespace App\Filament\Resources\AdmissionResource\Pages;

use App\Actions\FinalizeAdmissionAction;
use App\Filament\Resources\AdmissionResource;
use App\Services\Admissions\AdmissionDraftService;
use App\Services\Fees\ConcessionCalculator;
use App\Services\Fees\InstallmentPlanGenerator;
use App\Services\Fees\OfficialFeePlanData;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAdmission extends EditRecord
{
    protected static string $resource = AdmissionResource::class;

    protected static string $view = 'filament.resources.admission-resource.pages.edit-admission';

    public function getSubheading(): ?string
    {
        return 'Review the seven-step record, save a draft, or finalize the student and financial documents.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approveAndEnroll')
                ->label('Approve & Enroll')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status !== 'enrolled')
                ->action(function () {
                    try {
                        app(FinalizeAdmissionAction::class)
                            ->execute($this->record, filament()->auth()->id());
                        Notification::make()
                            ->title('Enrolled Successfully')
                            ->body('Student registered and fee vouchers generated.')
                            ->success()
                            ->send();
                        $this->redirect(route('admissions.complete', $this->record));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Enrollment Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
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
                ->label('Save Admission')
                ->submit('save'),
            Actions\Action::make('submitAdmission')
                ->label('Submit Admission')
                ->submit('save')
                ->color('gray'),
            Actions\Action::make('finalize')
                ->label('Submit and Generate Documents')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function () {
                    $this->save(shouldRedirect: false);
                    app(FinalizeAdmissionAction::class)
                        ->execute($this->record, filament()->auth()->id());
                    $this->redirect(route('admissions.complete', $this->record));
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! filament()->auth()->user()->hasRole('Super Admin')) {
            $data = array_merge($data, app(OfficialFeePlanData::class)->forAdmission($data));
            $data['concession_status'] = $this->record->concession_status;
        }

        if (($data['concession_type'] ?? 'none') !== 'none') {
            $money = app(InstallmentPlanGenerator::class);
            $packagePaisa = collect([
                'custom_tuition_fee',
                'custom_admission_fee',
                'custom_enrollment_fee',
                'custom_verification_fee',
                'custom_examination_fee',
                'custom_other_misc',
            ])->sum(fn (string $field) => $money->toPaisa($data[$field] ?? 0));
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

        return $data;
    }
}
