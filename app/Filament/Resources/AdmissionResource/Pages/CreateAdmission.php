<?php

namespace App\Filament\Resources\AdmissionResource\Pages;

use App\Actions\FinalizeAdmissionAction;
use App\Filament\Resources\AdmissionResource;
use App\Models\AdmissionDraft;
use App\Models\FranchisorStudentPayment;
use App\Services\Admissions\AdmissionDraftService;
use App\Services\Fees\ConcessionCalculator;
use App\Services\Fees\InstallmentPlanGenerator;
use App\Services\Fees\OfficialFeePlanData;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmission extends CreateRecord
{
    protected static string $resource = AdmissionResource::class;

    protected static string $view = 'filament.resources.admission-resource.pages.create-admission';

    public ?string $draftUuid = null;

    public string $autosaveStatus = 'Autosave ready';

    public function getHeading(): string
    {
        return 'Create Admission';
    }

    public function getSubheading(): ?string
    {
        return 'Add a new student admission record. Complete all steps and submit to generate documents.';
    }

    public function mount(): void
    {
        parent::mount();
        $this->draftUuid = request()->query('draft');

        if ($this->draftUuid) {
            $draft = AdmissionDraft::where('uuid', $this->draftUuid)
                ->where('created_by', filament()->auth()->id())
                ->firstOrFail();
            $this->form->fill($draft->payload);
        }
    }

    public function saveDraft(): void
    {
        $draft = app(AdmissionDraftService::class)->save(
            $this->form->getRawState(),
            filament()->auth()->user(),
            $this->draftUuid,
        );
        $this->draftUuid = $draft->uuid;

        Notification::make()
            ->success()
            ->title('Admission Draft Saved')
            ->body('Draft saved successfully. You can resume this application anytime using this URL.')
            ->send();
    }

    public function autosaveDraft(): void
    {
        $state = $this->form->getRawState();

        if (! $this->draftUuid && blank($state['applicant_name'] ?? null) && blank($state['cnic'] ?? null) && empty($state['student_photo'] ?? [])) {
            return;
        }

        try {
            $draft = app(AdmissionDraftService::class)->save(
                $state,
                filament()->auth()->user(),
                $this->draftUuid,
            );
            $this->draftUuid = $draft->uuid;
            $this->autosaveStatus = 'Saved '.now()->format('H:i:s');
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! filament()->auth()->user()->hasRole('Super Admin')) {
            $data = array_merge($data, app(OfficialFeePlanData::class)->forAdmission($data));
            $data['concession_status'] = ($data['concession_type'] ?? 'none') === 'none' ? 'approved' : 'pending';
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
            $data['concession_requested_by'] = filament()->auth()->id();
            $data['concession_requested_at'] = now();

            if (($data['concession_status'] ?? null) === 'approved') {
                $data['concession_approved_by'] = filament()->auth()->id();
                $data['concession_approved_at'] = now();
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $seatCost = $this->data['seat_cost'] ?? 0;
        $installments = $this->data['installments'] ?? [];

        if ($record->franchisor_id && $seatCost > 0) {
            $payment = FranchisorStudentPayment::create([
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
                    'is_published' => $inst['is_published'] ?? false,
                ]);
            }
        }

        $draft = $this->draftUuid
            ? AdmissionDraft::where('uuid', $this->draftUuid)->first()
            : null;
        app(FinalizeAdmissionAction::class)
            ->execute($record, filament()->auth()->id(), $draft);
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('autosaveStatus')
                ->label(fn () => $this->autosaveStatus)
                ->icon('heroicon-o-cloud')
                ->disabled()
                ->color('gray')
                ->extraAttributes(['class' => 'admission-action admission-action--autosave']),
            Actions\Action::make('saveDraft')
                ->label('Save Draft')
                ->icon('heroicon-o-bookmark')
                ->action('saveDraft')
                ->color('gray')
                ->extraAttributes(['class' => 'admission-action admission-action--draft']),
            Actions\Action::make('submitAdmission')
                ->label('Submit Admission')
                ->icon('heroicon-o-check')
                ->submit('create')
                ->color('gray')
                ->extraAttributes(['class' => 'admission-action admission-action--final admission-action--submit']),
            Actions\Action::make('submitAndGenerate')
                ->label('Submit & Generate Documents')
                ->icon('heroicon-o-check-circle')
                ->submit('create')
                ->extraAttributes(['class' => 'admission-action admission-action--final admission-action--generate']),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admissions.complete', $this->record);
    }
}
