<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page admission-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="admission-document-summary" role="status">
        <strong>{{ count($this->record->missingDocumentLabels()) }} missing documents</strong>
        <span>{{ implode(', ', $this->record->missingDocumentLabels()) ?: 'All checklist documents have been uploaded.' }}</span>
        <small>Saved record checklist. Save changes after uploading documents to update this count.</small>
    </div>


    @if(request()->boolean('review'))
        <div class="admission-return-notice" role="status">
            <span class="admission-return-notice__icon" aria-hidden="true">
                <x-filament::icon icon="heroicon-o-pencil-square" />
            </span>
            <span>
                <strong>Admission reopened for review</strong>
                <small>Use any Edit button to jump directly to that section. After making changes, return to Review &amp; Confirm and select Submit and Generate Documents.</small>
            </span>
        </div>
    @endif

    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="save"
    >
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>
