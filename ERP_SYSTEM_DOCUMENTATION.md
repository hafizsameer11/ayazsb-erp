# AyazSB ERP - Complete System Flow Documentation

## 1) Purpose of This Document

This document explains how the ERP works end-to-end in its current Laravel + MySQL implementation:

- what each module does
- how each screen behaves
- how each form field is processed
- what Save / Post / Print / Export actions do
- what data is stored in MySQL
- how role-based permissions control visibility and access

This is written as web-based product documentation for admin users, operators, developers, and QA.

---

## 2) High-Level Architecture

The system is a Laravel web ERP with these core layers:

1. **UI Layer (Blade Views)**  
   Legacy-style ERP forms, grids, toolbars, and action buttons.

2. **Controller Layer**  
   Validates input, checks permissions, calls business services, returns views/responses.

3. **Domain/Service Layer**  
   - `VoucherNumberService`: generates unique document numbers  
   - `PostingService`: changes status from `draft` to `posted`

4. **Data Layer (MySQL via Eloquent Models + Migrations)**  
   Stores masters, vouchers, inventory transactions, role/permission data.

5. **Authorization Layer (RBAC)**  
   Permissions are enforced both:
   - in UI (hide/show actions)
   - in backend endpoints (403 if unauthorized)

---

## 3) Modules and Main Navigation

Main modules:

- **Accounts & Finance**
- **Yarn Management**
- **Grey Management**
- **Reports**
- **Access Management (Admin)**

From the main menu, each module opens dashboard-style option groups and then screen-specific forms.

---

## 4) Authentication and Users

Custom auth is used (no Breeze/Fortify scaffolding).

- Login route: `/login`
- Logout route: `/logout`
- Root route redirects:
  - authenticated users -> accounts dashboard
  - guest users -> login

Default seeded users:

- `admin@erp.local` / `admin123` (Super Admin)
- `operator@erp.local` / `operator123` (Operator)

---

## 5) Role-Based Access Model

### Core RBAC entities

- `users`
- `roles`
- `permissions`
- pivot tables: `role_user`, `permission_role`, `permission_user`

### Permission naming pattern

`<module-or-screen>.<resource>.<action>`

Examples:

- `accounts.coa.create`
- `accounts.vouchers.jv.post`
- `yarn.issuance.create`
- `reports.accounts.print`
- `admin.roles.edit`

### Action-level control

Actions are standardized as:

- `view`
- `create`
- `edit`
- `delete`
- `post`
- `print`

The same permission model is applied to:

- screen access
- form submit endpoints
- post/print/export actions
- admin management actions

---

## 6) Database Design (Business Tables)

## 6.1 Shared Masters

- `parties`: business parties
- `godowns`: warehouse/storage points
- `items`: yarn/grey/shared items
- `financial_years`: FY definitions
- `accounts`: chart of accounts hierarchy
- `voucher_sequences`: running counters for document numbers

## 6.2 Accounts Transactions

- `account_openings`: opening balances
- `vouchers`: voucher header
- `voucher_lines`: voucher detail lines

## 6.3 Yarn/Grey Transactions

- `inventory_transactions`: transaction header
- `inventory_transaction_lines`: line items

All major transaction writes are wrapped in `DB::transaction(...)`.

---

## 7) Accounts & Finance - Detailed Screen Flows

## 7.1 Chart of Accounts (`/erp/accounts/coa`)

### Screen purpose

Create and view chart-of-account records by level:

- Head
- Control
- Ledger
- Sub Ledger

### Fields and behavior

- `Level` -> saved to `accounts.level`
- `Code` -> unique account code (`accounts.code`)
- `Name` -> account name (`accounts.name`)

When **Save Account** is clicked:

1. request validates required fields
2. uniqueness of code is checked
3. record is inserted into `accounts`
4. success message appears (`Account created.`)
5. lower table shows saved records

### About the tabbed grid (Head/Control/Ledger/Sub ledger)

In current implementation this lower grid is UI-assistive/legacy-style visual structure.  
Primary persisted flow is the top form (`Level`, `Code`, `Name`, `Save Account`).

---

## 7.2 Financial Year (`/erp/accounts/financial-year`)

### Fields

- `Year code` -> unique identifier
- `Start date`
- `End date`
- `Description`

### Save behavior

On **Add Year**:

1. validates dates and uniqueness
2. inserts into `financial_years`
3. list refreshes below

Stored columns include:

- `year_code`
- `start_date`
- `end_date`
- `description`
- `is_closed`

---

## 7.3 Accounts Opening (`/erp/accounts/opening`)

### Fields

- `Voucher date`
- `Financial year`
- `Account`
- `Debit`
- `Credit`
- `Narration`

### Validation rules

- cannot set both Debit and Credit > 0 in one opening line
- account and financial year must exist

### Save behavior

On **Add Opening**:

1. validates request
2. applies opening logic rules
3. inserts into `account_openings`
4. row appears in listing grid

---

## 7.4 Vouchers (JV/CP/CR/BPV/BRV/CV)

Routes:

- JV: `/erp/accounts/vouchers/jv`
- CP: `/erp/accounts/vouchers/cp`
- CR: `/erp/accounts/vouchers/cr`
- BPV: `/erp/accounts/vouchers/bpv`
- BRV: `/erp/accounts/vouchers/brv`
- CV: `/erp/accounts/vouchers/cv`

### Master section fields

- `Voucher date`
- `Voucher type` (readonly code)
- `Voucher num` (auto)
- `Fiscal year`

Optional UI fields (bank/cash layout variants) are present per voucher type view.

### Detail grid fields

Core persisted fields:

- `account_id`
- `description`
- `debit`
- `credit`
- `amount`
- `qty`, `rate`, `tag` (as applicable)

### Save voucher behavior

When save is submitted:

1. permission check: `accounts.vouchers.<type>.create`
2. validation of header + lines
3. number generation via `VoucherNumberService`
4. voucher header inserted as `status = draft`
5. detail lines inserted (`voucher_lines`)
6. totals calculated and written to header (`total_debit`, `total_credit`, `total_amount`)

### Posting behavior (critical)

When posting voucher:

1. permission check: `accounts.vouchers.<type>.post`
2. blocked if already posted
3. blocked if no detail lines
4. blocked if computed line totals <= 0
5. blocked if header totals do not match line sums
6. blocked if debit != credit
7. if valid -> `status = posted`, `posted_by`, `posted_at` updated

### Print behavior

Print action renders a print-ready voucher page with line details and totals.

---

## 8) Yarn and Grey Modules - Detailed Flow

These modules use a metadata-driven screen engine.

## 8.1 Screen loading

Each screen is defined by slug and label in `ModulePageController::MODULES`.

Examples:

- Yarn `issuance`
- Yarn `purchase-contract`
- Grey `purchase`
- Grey `conversion-inward`

## 8.2 Common transaction fields

Master:

- transaction date
- party
- remarks

Detail lines:

- item
- description
- qty
- unit
- weight (where relevant)
- rate
- amount

## 8.3 Save behavior

On save:

1. permission check `<module>.<screen>.create`
2. validation of line structure
3. transaction number generated via `VoucherNumberService::nextTransaction(...)`
4. header inserted into `inventory_transactions` (`draft`)
5. lines inserted into `inventory_transaction_lines`
6. `total_qty` and `total_amount` calculated and saved

## 8.4 Post behavior

On post:

1. permission check `<module>.<screen>.post`
2. verifies URL module/screen match actual transaction record
3. if not already posted -> status set to `posted`

## 8.5 Print behavior

Print opens transaction print template showing:

- module/screen
- transaction number/date/status
- party
- line details
- totals

---

## 9) Reports Module - Complete Behavior

Reports panel has three logical report screens:

- Accounts report (`accounts`)
- Yarn report (`yarn`)
- Grey report (`grey`)

Each report supports **three outputs**:

1. screen view
2. CSV export
3. print template

## 9.1 Filters

Standard filter parameters:

- `from_date`
- `to_date`
- additional optional parameters (`p1...p12`) for future extension

## 9.2 Data sources

- Accounts report -> `vouchers` (+ party relation)
- Yarn/Grey reports -> `inventory_transactions` (+ party relation)

## 9.3 Permission model for reports

- View route/action requires `reports.<screen>.view`
- Export + Print require `reports.<screen>.print`

Additionally, report screen slug is whitelisted (`accounts`, `yarn`, `grey`) to block unknown endpoints.

## 9.4 Output details

- **Screen view**: tabular summary with totals row
- **Export**: streamed CSV download
- **Print**: minimal print-ready HTML layout with totals

---

## 10) Access Management (Admin)

Admin routes provide:

- users list
- role assignment
- role CRUD
- role-permission matrix update

Only authorized users can access admin pages/actions via RBAC.

---

## 11) “What Each Button Does” Guide

This is a practical action map for business users:

- **Save Account**  
  Creates one account in COA.

- **Add Year**  
  Creates one financial year.

- **Add Opening**  
  Inserts one opening balance entry.

- **Save voucher**  
  Creates draft voucher + lines + totals.

- **Post voucher**  
  Locks voucher as posted after validation.

- **Voucher print / Print voucher**  
  Opens print layout for current voucher.

- **Save (Yarn/Grey forms)**  
  Saves draft inventory transaction with lines.

- **Post (Yarn/Grey recent transactions)**  
  Marks transaction as posted.

- **Print (Yarn/Grey recent transactions)**  
  Opens print-ready transaction page.

- **View report**  
  Renders on-screen report table.

- **Export CSV**  
  Downloads report as CSV file.

- **Print layout**  
  Opens printable report format.

---

## 12) Example Walkthrough Using Your Screenshot (COA)

You shared a **Chart of Accounts** screen where account creation succeeded.

How to interpret that screen:

1. Top breadcrumb identifies navigation path:  
   `Main menu -> Accounts & Finance -> Chart of Accounts`

2. Green message (`Account created.`) confirms backend insert succeeded.

3. In entry row:
   - choose Level (Head/Control/Ledger/Sub Ledger)
   - enter Code
   - enter Name
   - click **Save Account**

4. System validates:
   - code is required and unique
   - name is required

5. System writes into `accounts` table and refreshes page.

6. “Saved accounts” section shows newly inserted account.

Important: the lower tabbed editable grid is currently visual/legacy support UI; the main persisted save action is the top entry form.

---

## 13) Seed Data and Environment

Database bootstrap command:

- `php artisan migrate:fresh --seed`

Seeders executed:

- `RbacSeeder`
- `ErpMasterSeeder`
- `ErpTransactionSeeder`

This gives:

- users/roles/permissions
- basic masters (accounts/items/parties/godowns/year)
- sample transactions for quick report visibility

---

## 14) Technical Quality Status

Current status from verification:

- migrations/seeding: OK
- tests: passing
- build: passing
- route/view cache: successful
- reports (view/export/print): functional for all modules

---

## 15) Known UX Notes (Current Scope)

These are not failures, just implementation notes for future enhancement:

1. Some legacy-style UI controls are currently visual placeholders.
2. Account description auto-fill in voucher line grid can be enhanced with lookup modal/autocomplete.
3. Additional report parameters (`p1...p12`) are accepted in UI but currently not applied in report query logic.
4. Transaction screen master inputs can be upgraded to dropdown lookups for party/item/godown for better operator usability.

---

## 16) Recommended Next Enhancements

1. Add lookup/search popups for account and item selection.
2. Add edit/delete flows for vouchers and inventory transactions (with posted-state locks).
3. Add PDF export option in reports.
4. Add audit trail UI (created/posted by with timestamp detail).
5. Add dashboard KPIs (today vouchers, posted count, stock summary).

---

## 17) Quick Operational SOP

For daily operations:

1. Ensure current financial year exists.
2. Ensure masters are complete (accounts, parties, items, godowns).
3. Enter vouchers/transactions as draft.
4. Review totals and validations.
5. Post only verified documents.
6. Use reports for reconciliation and print/export for sharing.

---

## 18) File Pointers (For Developers)

Core business logic files:

- `app/Http/Controllers/Erp/AccountsFinanceController.php`
- `app/Http/Controllers/Erp/ModulePageController.php`
- `app/Http/Controllers/Erp/ReportController.php`
- `app/Services/VoucherNumberService.php`
- `app/Services/PostingService.php`
- `app/Support/PermissionRegistry.php`
- `routes/web.php`

Primary views:

- `resources/views/erp/accounts/*`
- `resources/views/erp/module-screen.blade.php`
- `resources/views/erp/reports/*`
- `resources/views/layouts/erp.blade.php`

---

## 19) Final Summary

This ERP is fully web-based and currently supports:

- secured login and RBAC
- accounts setup and voucher lifecycle
- yarn/grey transaction lifecycle
- full report outputs (screen, export, print)
- seeded operational baseline on MySQL

This document can be used as the baseline product handbook for users and implementers.

