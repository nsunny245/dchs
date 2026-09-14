# DGC ZKTeco K50 and Bulk Admissions Build Plan

Status: Proposed for approval before implementation  
Project: Daniyal Group of Colleges College Management System  
Prepared: 9 September 2026

## 1. Objectives

This build has two connected objectives:

1. Integrate ZKTeco K50 fingerprint attendance terminals with student and teacher attendance across every campus.
2. Add a safe, fast bulk-admission workflow for importing more than 700 existing students without forcing staff through the full admission wizard one student at a time.

The existing Laravel, Filament, campus-scoping, admission, enrollment, fee, student-attendance, and teacher-attendance architecture will be preserved.

## 2. Confirmed Current Architecture

- Student attendance uses `attendances`, scoped by `student_id`, `campus_id`, `course_id`, and date.
- Teacher attendance uses `teacher_attendances`, scoped by `staff_id`, `campus_id`, and date.
- Staff already has a `biometric_id` field.
- Students do not yet have a safe device-user mapping.
- Normal admission finalization uses `EnrollmentService` and creates a student login, enrollment number, fee snapshot, fee account, admission-fee voucher, tuition-installment vouchers, and audit record in one transaction.
- The admission list header is implemented in `ListAdmissions::getHeaderActions()` and currently provides New Admission and Resume Draft.
- The normal admission database requires identity/contact fields beyond the requested bulk fields, including CNIC/B-Form, date of birth, gender, phone, and address.

## 3. ZKTeco K50 Integration Architecture

### 3.1 Recommended topology

The K50 will remain inside the campus LAN. A small Windows background service on an always-on campus computer will communicate with the terminal over TCP/IP and send normalized events to the hosted DGC application over outbound HTTPS.

```text
ZKTeco K50 -> Campus LAN TCP/4370 -> DGC Sync Agent -> HTTPS -> Laravel API
```

No device port will be exposed publicly. Shared hosting will not attempt to initiate a connection into the campus network.

### 3.2 Required database additions

#### `biometric_devices`

- campus ID
- device code, name, model, serial number, and firmware
- local IP address and port
- encrypted communication key
- timezone
- active/offline/error state
- last seen, last pull, and last successful sync times
- last error summary

#### `biometric_person_mappings`

- campus ID and device ID
- person type: student or staff
- person record ID
- device user ID
- enrollment/synchronization timestamps
- active flag
- unique constraint on device plus device-user ID
- unique constraint on device plus person type plus person ID

#### `biometric_punch_logs`

- campus and device
- device-user ID
- punch timestamp and received timestamp
- punch state, verification method, and work code
- raw payload
- deterministic event fingerprint for duplicate protection
- matched person type and person ID
- processing state and error

#### Attendance extensions

- source: manual, biometric, or imported
- source device ID
- first check-in and last check-out
- working, late, and early-departure minutes
- manual override flag, reason, actor, and timestamp

Raw punches will never be overwritten when a daily attendance record is corrected.

### 3.3 Laravel components

- Device registry resource for Super Admin.
- Campus device status page for Campus Admin.
- Student and staff biometric-mapping actions.
- Versioned authenticated ingestion API.
- Idempotent punch-ingestion service.
- Punch-to-daily-attendance processor.
- Failed/unmapped punch review queue.
- Device heartbeat and health widget.
- Audit logs for mapping, correction, retry, and device configuration changes.

### 3.4 Campus sync agent

The reusable Windows agent will:

- connect to one or more local devices;
- remember the last downloaded transaction;
- queue records locally when internet is unavailable;
- send batches using signed HTTPS requests;
- retry safely without creating duplicates;
- run automatically after Windows starts;
- expose a small local diagnostics window;
- keep rotating logs without storing fingerprint templates.

The connector will normalize device events so the same agent can later send data to another Laravel, Node, ERP, HR, or payroll system by changing its destination adapter.

### 3.5 Hardware commissioning checklist

1. Confirm exact model, serial number, firmware, and whether ADMS is available.
2. Assign a DHCP reservation/static IP on the campus LAN.
3. Confirm subnet, gateway, device time, and Asia/Karachi timezone.
4. Set a non-default communication key.
5. Confirm TCP communication from the connector computer, normally on port 4370.
6. Enroll test users using the same numeric IDs stored in DGC mappings.
7. Test offline punches, restart recovery, duplicates, and incorrect mappings.
8. Put the device, router, and connector on backup power where practical.

### 3.6 Security and privacy

- Fingerprint templates remain on the device unless a separately reviewed vendor-supported transfer is required.
- DGC receives identity references and punch events, not fingerprint images.
- Every connector gets its own revocable credential.
- Requests use HTTPS, timestamp, nonce, signature, and idempotency key.
- Device communication keys are encrypted at rest.
- Campus scoping is enforced server-side, not trusted from the submitted payload alone.
- Raw events and manual corrections are retained for audit according to a defined retention policy.

## 4. Bulk Admissions Concept

### 4.1 Entry point

The Admissions page header will contain:

- New Admission
- Bulk Admissions
- Resume Draft

`Bulk Admissions` opens a dedicated full-width page rather than a small modal.

### 4.2 Recommended two-mode page

#### Mode A: Quick Entry Grid

Designed for tens of records entered manually.

- Campus is automatically fixed to the Campus Admin's campus; Super Admin must select a campus.
- A compact toolbar contains common defaults: session, program, fee plan, and qualification.
- The editable grid initially shows one row.
- `Add Row` adds another row and focuses the Student Name field.
- `Add 10 Rows` supports faster typing.
- Copy Previous duplicates session/program/fee-plan defaults, never the student name or father name.
- Rows can be reordered or removed before validation.
- Sticky header and sticky footer keep columns and actions visible.

Required visible columns:

1. Student Name
2. Father Name
3. Qualification
4. Session
5. Program
6. Fee Plan

System columns shown after validation:

- Row number
- Validation result
- Duplicate warning
- Import result/enrollment number

#### Mode B: Excel/CSV Import

Recommended for the 700-student migration.

- Download an official DGC template.
- Upload `.xlsx` or `.csv`.
- Map file columns to DGC fields when headings differ.
- Preview and validate all rows before any record is written.
- Filter valid, warning, and invalid rows.
- Download an error workbook containing row numbers and correction messages.
- Import valid rows only after an explicit confirmation screen.
- Process the import in chunks/jobs so shared hosting does not time out.
- Show progress, imported count, failed count, and resumable batch status.

### 4.3 Import record policy

The six requested fields will create a **basic imported student profile**, not silently imitate a completed seven-step admission.

Recommended behavior:

- Generate the official campus/program enrollment number using the existing sequence rules.
- Create an internal student user with a random locked password and generated internal email; login remains disabled until identity/contact information is completed.
- Store the selected session, program, qualification, campus, and source batch.
- Mark the profile as `profile_incomplete` through a separate completeness flag, not a confusing admission status.
- Do not invent CNIC, date of birth, gender, phone, address, photographs, or documents.
- Do not generate admission agreements or ID cards until the necessary profile fields are completed.
- Allow Campus Admin to open `Complete Profile`, which starts the normal admission editor with imported values prefilled.

### 4.4 Financial policy requiring approval

The recommended safe default is:

- Selecting a fee plan during import records the intended plan.
- It does **not** generate vouchers automatically for historical students.
- A second financial-import stage can capture opening package, concession, amount already paid, remaining balance, installment count, and next due date.
- Only after financial preview confirmation will fee accounts and future vouchers be created.

This avoids creating 700 full new fee schedules that ignore money already collected before the system was introduced.

Alternative, not recommended: immediately apply the current official fee plan and generate new admission/installment vouchers for every imported row.

### 4.5 Duplicate prevention

Because basic rows may not contain CNIC, duplicate detection will use layered checks:

- exact existing enrollment number when supplied;
- campus plus normalized student name plus normalized father name plus program plus session;
- optional phone/CNIC when later supplied;
- duplicates within the same uploaded file;
- a warning/override workflow rather than silent duplication.

Each import row receives an idempotency key so retrying a timed-out job cannot create another student.

### 4.6 Import database additions

#### `student_import_batches`

- UUID, campus, uploaded by, source filename
- status and progress counters
- defaults and configuration snapshot
- started, completed, cancelled timestamps
- error report path

#### `student_import_rows`

- batch and source row number
- raw data and normalized data
- validation errors/warnings
- processing status
- resulting admission/student IDs
- idempotency key and processing timestamps

#### Existing table adjustments

- Add student profile-completeness fields.
- Add academic-session relationship to student if not already represented safely.
- Add import source/batch references.
- Avoid making fake values for currently required admission identity columns by using a dedicated bulk-enrollment service rather than inserting incomplete normal admissions.

### 4.7 Processing architecture

- `BulkStudentImportService` validates and normalizes rows.
- `BulkStudentEnrollmentService` creates basic student/user records transactionally.
- Current campus-code and enrollment-number logic will be extracted into one reusable generator used by both normal and bulk enrollment.
- Each row uses its own transaction so one invalid student does not roll back the entire batch.
- Batch jobs run in chunks of approximately 25-50 rows, adjusted after shared-hosting load tests.
- Importing 700 rows will not be performed in one browser request.

## 5. Screen and Interaction Specification

### Header

Title: Bulk Admissions  
Subtitle: Add existing students quickly by grid or spreadsheet.  
Actions: Download Template, Import History, Back to Admissions.

### Quick Entry

- Full-width responsive card using the established navy/gold visual system.
- Desktop: six editable columns plus row actions.
- Tablet/mobile: each row becomes a labeled expandable card; no inaccessible horizontal actions.
- Inline validation appears beneath the affected field.
- Unsaved-change warning protects typed rows.

### Spreadsheet Preview

- Summary cards: total, ready, warnings, blocked.
- Full-width preview table with frozen identifying columns.
- Search and validation filters.
- No import button until blocked rows are resolved or excluded.
- Confirmation states exactly what will and will not be created.

### Completion

- Imported/failed totals.
- Links to created students.
- Downloadable error file.
- `Complete Missing Profiles` filtered action.
- `Start Financial Opening Balance Import` action when approved.

## 6. Implementation Sequence

### Phase 1: Decisions and device proof

- Approve bulk financial behavior.
- Confirm K50 firmware/protocol with a real device test.
- Obtain one attendance export and one TCP connection test.

### Phase 2: Shared foundations

- Migrations for devices, mappings, punch logs, import batches/rows, and student completeness.
- Shared campus-aware enrollment-number generator.
- Policies, permissions, audit events, factories, and rollback-safe migrations.

### Phase 3: Bulk admissions

- Header action and routes.
- Quick Entry Grid.
- Template download and spreadsheet upload.
- Preview, validation, duplicate detection, queued import, progress, and error export.
- Complete Profile workflow.
- Optional financial opening-balance stage after separate approval.

### Phase 4: K50 server integration

- Device resources and credential issuance.
- Punch/heartbeat API.
- Idempotent ingestion and daily attendance processing.
- Mapping and exception-review screens.

### Phase 5: Windows connector

- K50 adapter proof of concept.
- Encrypted configuration, offline queue, service installation, diagnostics, and auto-update policy.
- Pilot package and campus installation guide.

### Phase 6: Verification and rollout

- Automated tenancy, duplicate, fee-safety, API-authentication, retry, and attendance tests.
- Browser verification at mobile, tablet, laptop, and wide-screen widths.
- Pilot one campus and one K50 for five working days.
- Validate daily totals against the device report.
- Roll out campus by campus with rollback and support instructions.

## 7. Acceptance Criteria

### Bulk admission

- 700 rows can be validated without a browser timeout.
- Retrying an import creates no duplicates.
- Campus Admin cannot import into another campus.
- Program, session, and fee plan must belong to the selected campus context.
- Invalid rows never produce partial financial records.
- No voucher is generated unless the approved financial mode explicitly requires it.
- Every imported record is traceable to batch, row, user, and time.

### Biometric attendance

- A punch reaches DGC within 60 seconds under normal connectivity.
- Offline punches synchronize after connectivity returns.
- Repeated delivery produces one raw event and one attendance effect.
- Student and teacher IDs cannot collide across devices/campuses.
- Unknown users appear in an exception queue rather than attaching to the wrong person.
- Manual corrections preserve the original punch and correction audit.
- Device credentials can be revoked independently.

## 8. Decisions Required Before Coding

1. Approve the two-mode Bulk Admissions page: Quick Entry plus Excel/CSV Import.
2. Approve basic imported profiles with `Complete Profile`, rather than invented CNIC/contact data.
3. Choose financial behavior:
   - **Recommended:** import profile first; add historical opening balances in a second reviewed stage.
   - **Alternative:** immediately generate a fresh fee schedule from the selected fee plan.
4. Confirm whether existing enrollment numbers will be included in the spreadsheet or generated by DGC.
5. Confirm the always-on Windows computer available for the first K50 pilot.
6. Supply K50 serial/firmware/COMM-menu photographs or arrange an on-campus TCP/USB test.

No production implementation or live deployment should begin until decisions 1-4 are approved and the K50 protocol has been verified.
