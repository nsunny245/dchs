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
        .admission-page .fi-form-actions {
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 1rem !important;
            margin-top: 1.5rem !important;
            padding: 1rem 1.25rem !important;
            background: #ffffff !important;
            border: 1px solid var(--dgc-border, #E2E8F0) !important;
            border-radius: 0.75rem !important;
            position: relative !important;
            bottom: auto !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
        }
    </style>
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
