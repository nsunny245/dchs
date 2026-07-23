# Daniyal Group of Colleges — Codex Project Context

## Project identity

This is the **Daniyal Group of Colleges College Management System**.

Use the official **Daniyal Group of Colleges** logo and the tagline **“Where Success Is a Tradition”** across all shared group-level interfaces and generated documents.

Do not use the Daniyal College of Health Sciences logo unless a future page is explicitly campus-specific and requires that identity.

Apply the group logo to:

- Login pages
- Admin dashboard sidebar and header
- Admission workflow
- Student profiles
- Admission confirmation screens
- Fee vouchers and receipts
- Agreements and undertakings
- Printed admission forms
- Student ID cards
- Reports and PDFs
- Favicon
- Email and notification templates

## Technology

- Laravel 12
- Livewire
- Filament where already used
- MySQL
- Blade components
- Existing Tailwind/CSS design system
- Laravel Policies and Gates
- Laravel Storage for admission documents
- Jobs/queues for heavy PDF or voucher generation when needed

Preserve the current architecture and existing data. Do not replace the application with a different framework.

## Design language

The interface must feel like a premium institutional college administration system.

Use:

- Deep navy and gold branding
- White content surfaces
- Soft grey page background
- Clean typography
- Compact but readable forms
- Subtle borders and restrained shadows
- Consistent spacing
- Strong visual hierarchy
- Responsive and accessible components

Avoid:

- Generic CRUD styling
- Ecommerce-style layouts
- Excessive gradients
- Bright school-portal colors
- Healthcare-only branding
- Visually overloaded ERP screens

## Brand tokens

```css
:root {
    --dgc-navy-950: #06192E;
    --dgc-navy-900: #082245;
    --dgc-navy-800: #10345D;
    --dgc-navy-700: #1B4775;

    --dgc-gold-700: #B97708;
    --dgc-gold-600: #C98D18;
    --dgc-gold-500: #E0A126;
    --dgc-gold-400: #E7B65A;
    --dgc-gold-200: #F3D79F;
    --dgc-gold-100: #FCF5E6;

    --dgc-page-bg: #F4F7FB;
    --dgc-surface: #FFFFFF;
    --dgc-surface-soft: #F8FAFC;
    --dgc-border: #D9E2EC;
    --dgc-border-strong: #BAC8D8;

    --dgc-text-primary: #102033;
    --dgc-text-secondary: #5E6D7E;
    --dgc-text-muted: #8290A3;

    --dgc-success: #16855B;
    --dgc-success-bg: #EAF8F2;
    --dgc-warning: #B7791F;
    --dgc-warning-bg: #FFF7E8;
    --dgc-danger: #C43D4B;
    --dgc-danger-bg: #FFF0F2;
    --dgc-info: #2563A8;
    --dgc-info-bg: #EEF6FF;
}
```

Preferred fonts: Inter, Plus Jakarta Sans, or Manrope.

## Admission workflow

The admission wizard contains seven steps:

1. Student Photo
2. Student Information
3. Parent or Guardian
4. Academic Details
5. Documents Vault
6. Course and Fee Plan
7. Review and Confirm

Users must be able to save a draft, leave, resume later, move backward without data loss, see step validation, and submit only when required data is complete.

## Infographic arrow stepper

Replace the current progress bar with seven connected arrow-shaped segments.

Each arrow contains:

- Step number
- Icon
- Step title
- Status label

States:

- **Completed:** navy background, white text, white check icon, “Completed” label
- **Current:** gold background, deep navy text, white circular icon container, “Current Step” label, slight elevation
- **Upcoming:** white/light-grey background, muted navy-grey text and icon, soft border
- **Error:** pale red background, red border and alert icon

Suggested CSS shape:

```css
.admission-step {
    position: relative;
    min-height: 88px;
    padding: 16px 30px 14px 38px;
    clip-path: polygon(
        0 0,
        calc(100% - 22px) 0,
        100% 50%,
        calc(100% - 22px) 100%,
        0 100%,
        22px 50%
    );
}
```

The first arrow has a flat left edge. The last arrow has a clean right edge.

On mobile, use:

- “Step X of 7”
- Current step title
- Completion percentage
- Progress bar
- Optional horizontally scrollable compact navigation

## Page layout

Use a centered container around 1280px wide.

```css
.admission-page {
    max-width: 1280px;
    margin: 0 auto;
    padding: 28px 32px 48px;
}
```

Desktop layout:

- Main form: about 75%
- Context summary panel: about 25%

Tablet/mobile:

- Single-column form
- Summary panel moves below the form
- Sticky bottom actions

## Form styling

Use consistent 46px minimum input height, 10px radius, one dropdown chevron, clear labels, visible focus states, and inline validation.

```css
.form-control {
    min-height: 46px;
    border: 1px solid var(--dgc-border);
    border-radius: 10px;
    background: var(--dgc-surface);
    padding: 10px 13px;
}

.form-control:focus {
    border-color: var(--dgc-gold-600);
    box-shadow: 0 0 0 3px rgba(201, 141, 24, 0.14);
}
```

Fix the current repeated-chevron bug. Each select must show exactly one dropdown arrow.

Use a two-column desktop grid and one column on smaller screens.

## Step requirements

### 1. Student Photo

Provide a structured upload card with:

- 4:5 portrait preview
- Upload, replace, remove
- Crop and reposition
- Camera upload on supported devices
- File type and size validation
- White/light-blue background guidance

Controls must not overlap the student’s face.

### 2. Student Information

Group fields into:

- Personal Identity
- Contact Details
- Admission Preferences

Use a two-column layout. Address fields span full width.

### 3. Parent or Guardian

Include:

- Father/guardian name
- Relationship to student
- CNIC
- Mobile
- Occupation
- Mother CNIC
- Mother contact
- Emergency contact
- Guardian address

Add a switch: “Guardian address is the same as student address.”

Apply Pakistani CNIC and phone formatting.

### 4. Academic Details

Use compact expandable qualification cards with:

- Academic level
- Board/university
- Roll number
- Passing year
- Total marks
- Obtained marks
- Auto-calculated percentage
- Grade
- Drag handle
- Collapse/expand
- Duplicate
- Delete

Formula:

```text
Percentage = Obtained Marks ÷ Total Marks × 100
```

### 5. Documents Vault

Use a responsive document checklist grid.

Each document card includes:

- Document title
- Required/optional status
- Upload
- Preview
- Replace
- Download
- Remove
- Verification status
- File name/type/size
- Uploaded timestamp
- Reviewer note

Statuses:

- Missing
- Pending
- Uploaded
- Under Review
- Verified
- Rejected

### 6. Course and Fee Plan

Fields:

- Campus
- Academic session
- Course/program
- Admission date
- Shift where applicable

Load the official fee structure automatically after course selection.

Fee groups:

- One-time charges
- Recurring tuition
- Additional charges
- Scholarship/concession

Concession fields:

- Type
- Fixed or percentage value
- Reason
- Approved by
- Approval reference
- Supporting attachment

Permissions:

- Only super administrators can create/edit master fee structures
- Campus staff can assign approved plans and request concessions
- All overrides and approvals must be logged

Live fee summary:

- Official package total
- Concession
- Net payable
- Payable at admission
- Remaining balance
- Number of installments
- Installment amount
- First due date

Add an installment timeline and “Preview All Vouchers”.

### 7. Review and Confirm

Show review cards for:

- Student profile
- Personal information
- Guardian information
- Academic qualifications
- Documents
- Course assignment
- Fee and installment plan

Each section includes completion status, key values, warnings, and an Edit action.

Add a declaration checkbox.

Final actions:

- Save as Draft
- Submit Admission
- Submit and Generate Documents

The primary final action is **Submit and Generate Documents**.

## Sticky action footer

Normal steps:

```text
Back | Autosave Status | Save Draft | Save and Continue
```

Final step:

```text
Back | Save Draft | Submit Admission | Submit and Generate Documents
```

Do not show “Create” or “Create & create another” before final submission.

## Right-side context panel

Desktop panel should include:

- Progress Overview
- Admission Summary
- Context-specific Quick Tips

Admission summary fields:

- Student name
- Selected course
- Campus
- Academic session
- Shift

Move this panel below the form on tablet/mobile.

## Functional requirements

Implement or preserve:

- Autosave
- Save as draft
- Resume incomplete admission
- Step-level validation
- Error summary
- Inline validation
- Unsaved changes warning
- Destructive-action confirmations
- CNIC and phone formatting
- File validation
- Academic percentage calculation
- Fee calculation
- Role-based fee permissions
- Concession and override audit trails
- Installment generation
- Voucher generation
- Keyboard accessibility
- Screen-reader labels
- Reduced-motion support
- Responsive layouts
- No horizontal overflow

## Admission completion transaction

Final submission must run inside a database transaction and:

1. Generate admission number
2. Create/finalize student profile
3. Assign campus
4. Assign academic session
5. Assign course/program
6. Save a fee-structure snapshot
7. Apply approved concession
8. Generate installment schedule
9. Generate vouchers
10. Create opening student-ledger entries
11. Save document verification states
12. Generate admission documents
13. Store an audit trail
14. Redirect to a completion screen

## Generated documents

Use Daniyal Group of Colleges branding on:

- Admission form
- Student profile sheet
- Fee agreement
- Student undertaking
- Admission receipt
- Installment schedule
- Complete voucher book
- Missing-document slip
- Student ID-card data sheet
- Guardian information sheet

## Voucher specification

Voucher set may include:

- Bank Copy
- Accounts Office Copy
- Student Copy

Each voucher shows:

- Daniyal Group of Colleges logo
- Institution name and campus
- Student name
- Father/guardian name
- Admission/registration number
- Course and academic session
- Installment number
- Issue and due dates
- Fee breakdown
- Previous balance
- Late fee where applicable
- Net payable
- Voucher number
- Payment status
- Barcode/QR code where implemented
- Authorized signature area

## Suggested Laravel domains

```text
app/
├── Domain/
│   ├── Admissions/
│   ├── Students/
│   ├── Academics/
│   ├── Documents/
│   ├── Fees/
│   ├── Payments/
│   └── Vouchers/
├── Filament/
│   ├── Resources/
│   ├── Pages/
│   └── Widgets/
├── Models/
├── Services/
├── Actions/
├── Policies/
└── Support/
```

Suggested services:

```text
AdmissionWorkflowService
AdmissionDraftService
StudentRegistrationService
FeeStructureResolver
InstallmentPlanGenerator
VoucherGenerationService
PaymentAllocationService
ConcessionApprovalService
AdmissionDocumentGenerator
AdmissionAuditService
```

## Codex implementation rules

- Inspect the existing code before changing architecture
- Reuse existing models and relationships where safe
- Avoid destructive migrations
- Preserve existing data
- Use new migrations for schema changes
- Use transactions for final admission creation
- Keep business logic out of Blade templates
- Avoid one oversized Livewire component
- Extract calculations into services/actions
- Add policies for fees and concessions
- Log overrides and approvals
- Validate at UI and service layers
- Add automated tests for calculations, permissions, drafts, vouchers, and submission

## Delivery order

1. Audit current workflow and database
2. Apply correct group branding
3. Add design tokens and reusable components
4. Replace stepper with arrow infographic
5. Fix repeated select chevrons
6. Rebuild responsive form layouts
7. Add sticky action footer
8. Add autosave and draft recovery
9. Redesign student, guardian, and academic steps
10. Redesign document vault
11. Implement fee structure resolution
12. Implement concession approvals
13. Implement installments
14. Implement vouchers
15. Implement review and confirmation
16. Generate admission documents
17. Add permissions and audit trails
18. Add tests
19. Run responsive and accessibility QA

## Acceptance criteria

The work is complete when:

- Correct Daniyal Group of Colleges branding is used throughout
- No Health Sciences logo remains on shared group-level screens
- Arrow stepper supports completed/current/upcoming/error states
- Mobile progress layout works properly
- Select controls show one chevron only
- Forms are responsive without overflow
- Drafts can be saved and resumed
- Official fee plans load correctly
- Unauthorized users cannot edit master fees
- Concessions and overrides are auditable
- Installments and vouchers are generated correctly
- Review screen shows all admission sections
- Final submission creates all student and financial records
- Generated PDFs use the correct logo
- Critical business rules are covered by tests

## Final design intent

The system should feel institutional, trustworthy, modern, efficient, and purpose-built for Daniyal Group of Colleges. Staff must always understand the current step, completed work, missing information, financial commitments, and the documents that will be generated.
