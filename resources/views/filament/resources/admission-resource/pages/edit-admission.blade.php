<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page admission-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <style>
        .admission-page .fi-form,
        .admission-page .fi-form > div,
        .admission-page .fi-fo-wizard {
            grid-column: 1 / -1 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .admission-page .fi-fo-wizard-header {
            display: grid !important;
            grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
            width: 100% !important;
        }
        .admission-page .admission-split-grid > .fi-fo-component-ctn {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 320px) !important;
            gap: 1.25rem !important;
            width: 100% !important;
        }
        .admission-page .admission-photo-step .filepond--root {
            max-width: 100% !important;
            width: 100% !important;
        }
    </style>

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
