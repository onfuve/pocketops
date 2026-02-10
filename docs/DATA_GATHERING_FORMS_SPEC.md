# Data Gathering Forms — Feature Spec

Dynamic web forms built from modules. Manager creates a form, gets a link; customer fills and submits; after a configurable edit window the link becomes read-only or inaccessible. Manager sees all submissions in an inbox.

---

## 1. Link & submission modes (both supported)

| Mode | Description | Use case |
|------|-------------|----------|
| **One link = one submission** | Each form instance has one unique link; one customer fills it once. | Send one link per customer (e.g. per invoice, per order). |
| **One link = many submissions** | Same form, same link; we identify customer (e.g. mobile) first, then create one submission per customer. | Public form: هر مشتری با موبایل خود یک بار پر می‌کند. |

Implementation: form has a flag or type like `single_submission` vs `multi_submission`. For multi, we need an identifier step (e.g. “شماره موبایل”) before showing the form; we create one `form_submission` per identifier.

---

## 2. Edit period after submit (configurable)

- **Trigger**: Edit window starts **from when the customer submits** (not from first open).
- **Behaviour**: After submit, customer can still open the link and edit for X minutes. After X minutes, the URL is no longer accessible (or read-only) from customer side.
- **Config**: Manager defines the **edit period** per form (e.g. 0, 5, 15, 30, 60 minutes).  
  - 0 = no edit after submit (link dead right after submit).

So: store `submitted_at` on submission; allow access while `now() <= submitted_at + edit_period_minutes`; after that, show “مهلت ویرایش تمام شده” and block edit (or redirect).

---

## 3. Modules: custom text and labels

- Modules are **typed** (upload, address, consent, survey, etc.) but **labels and text are custom** per form.
- Each module has:
  - **Type** (e.g. `file_upload`, `postal_address`, `consent`, `survey`, `custom_fields`).
  - **Config (JSON)**: labels, placeholder text, help text, required flags, options for choices, file types, etc.
- Optional **custom text / rich text** block as its own module type (e.g. “متن توضیحات” or “قوانین”) so the manager can add paragraphs above/between modules.

So: no fixed labels; manager sets title, description, field labels, and any custom text when building the form.

---

## 4. Optional link to Contact, Lead, Task

- A **submission** can optionally be linked to:
  - a **Contact**,
  - a **Lead**,
  - a **Task**.
- Not required: forms can be used standalone.
- When useful: manager creates form from contact/lead/task context and stores `contact_id` / `lead_id` / `task_id` on the form or submission so that in the inbox they can jump to the related record.

Implementation: `form_submissions` (or `forms` if one-to-one) has nullable `contact_id`, `lead_id`, `task_id`. Inbox can filter and show “Related to: [Contact X]”.

---

## 5. Data model (summary)

- **forms**  
  - user_id, title, slug/code, status (draft | active | closed),  
  - submission_mode: single | multi,  
  - edit_period_minutes (0 = no edit after submit),  
  - optional: contact_id, lead_id, task_id (for context when creating the form),  
  - timestamps.

- **form_modules**  
  - form_id, sort_order, type (file_upload | postal_address | consent | survey | custom_text | custom_fields | …),  
  - config (JSON): labels, text, required, file_types, questions, etc.

- **form_submissions**  
  - form_id,  
  - identifier (e.g. mobile for multi mode; null for single),  
  - first_accessed_at, submitted_at, last_activity_at,  
  - optional: contact_id, lead_id, task_id,  
  - data (JSON or normalized): per-module answers and file refs.

- **Attachments**  
  - For file-upload modules: attach to submission (e.g. attachable_type = FormSubmission, attachable_id = submission id) or dedicated form_submission_files table.

---

## 6. Flows (short)

- **Manager**: Create form → add modules (with custom labels/text) → set edit period → activate → get link(s). For single mode: optionally “Create another link” from same form. View inbox → list submissions → open submission detail (and optionally go to related contact/lead/task).
- **Customer**: Open link → (multi: enter mobile/identifier) → fill modules → submit → (if edit period > 0) can reopen and edit until period ends → after that, link inaccessible or read-only.

---

## 7. Implementation phases (unchanged)

- **Phase 1**: Forms + modules (types + config with custom labels/text), public form page, submit and store; single-submission mode only; no edit period yet.
- **Phase 2**: Edit period from submit (configurable per form); “edit window expired” state.
- **Phase 3**: Manager inbox (list + detail); optional contact/lead/task link on submission.
- **Phase 4**: Multi-submission mode (identifier step); form templates; more module types if needed.

---

This spec reflects: (1) both link modes, (2) edit period from submit with manager-defined duration, (3) custom text and labels for modules, (4) optional link to contact, lead, or task.
