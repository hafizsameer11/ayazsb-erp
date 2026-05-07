@extends('layouts.erp')

@section('title', 'ERP Documentation')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            SYSTEM GUIDE — Complete ERP Flow (Web Documentation)
        </div>

        <div class="space-y-4 p-4 text-[13px] leading-6 text-slate-800">
            <section class="rounded border border-slate-300 bg-[#f9f9f9] p-3">
                <h2 class="mb-2 text-[14px] font-bold uppercase">How to use this page</h2>
                <p>
                    This is the official business + functional handbook for this ERP. It explains the business model first, then maps every module/screen to
                    practical operations, data entries, approvals, and outputs.
                </p>
                <p class="mt-2">
                    URL for this guide: <span class="font-mono">{{ route('erp.docs') }}</span>
                </p>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">1) Business Model - What this ERP is for</h3>
                <div class="rounded border border-slate-300 p-3">
                    <p><strong>Industry context:</strong> textile/weaving operations where yarn is purchased/issued/processed and grey cloth is purchased/sold/converted.</p>
                    <p class="mt-2"><strong>Main goal:</strong> one integrated system for financial control + stock/production movement + reporting + role-based control.</p>
                    <p class="mt-2"><strong>Core business cycle:</strong></p>
                    <ol class="list-decimal pl-6">
                        <li>Create masters (accounts, parties, godowns, items, financial year).</li>
                        <li>Record daily business transactions as draft.</li>
                        <li>Validate and post approved entries.</li>
                        <li>Track balances and movement in reports.</li>
                        <li>Export/print for audit, management, and compliance.</li>
                    </ol>
                </div>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">2) Department-wise usage</h3>
                <div class="overflow-x-auto border border-slate-300">
                    <table class="w-full border-collapse text-left text-[12px]">
                        <thead>
                            <tr class="bg-[#d8d8d8]">
                                <th class="border border-slate-400 px-2 py-1">Department</th>
                                <th class="border border-slate-400 px-2 py-1">Uses which module</th>
                                <th class="border border-slate-400 px-2 py-1">Main objective</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="border border-slate-300 px-2 py-1">Accounts</td><td class="border border-slate-300 px-2 py-1">Accounts &amp; Finance</td><td class="border border-slate-300 px-2 py-1">GL control, vouchers, opening balances, year controls.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Stores/Inventory</td><td class="border border-slate-300 px-2 py-1">Yarn + Grey</td><td class="border border-slate-300 px-2 py-1">Stock movement, issue/receipt/transfer records.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Sales/Purchase Ops</td><td class="border border-slate-300 px-2 py-1">Yarn + Grey transactions</td><td class="border border-slate-300 px-2 py-1">Contract and non-contract transactional entries.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Management</td><td class="border border-slate-300 px-2 py-1">Reports</td><td class="border border-slate-300 px-2 py-1">Decision support via filtered report summaries and exports.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">IT/Admin</td><td class="border border-slate-300 px-2 py-1">Access Management</td><td class="border border-slate-300 px-2 py-1">Role templates, permission governance, user control.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">3) System Modules</h3>
                <ul class="list-disc pl-6">
                    <li><strong>Accounts &amp; Finance:</strong> COA, financial year, openings, voucher workflow.</li>
                    <li><strong>Yarn Management:</strong> contracts, issuance/receipt/transfer/gain-short transactions.</li>
                    <li><strong>Grey Management:</strong> purchase/sale/conversion/opening transactions.</li>
                    <li><strong>Reports:</strong> Accounts/Yarn/Grey reports with on-screen, CSV export, print layout.</li>
                    <li><strong>Access Management:</strong> users, roles, permissions matrix.</li>
                </ul>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">4) Accounts - Screen by Screen (Business + Technical)</h3>
                <div class="space-y-2 rounded border border-slate-300 p-3">
                    <p><strong>Chart of Accounts:</strong> this is your accounting structure. Without valid COA, voucher posting cannot be meaningful. Top form is operational save flow; lower tab grid is legacy UI visual support.</p>
                    <p><strong>Financial Year:</strong> controls transaction period. Every voucher/opening is tied to one year for period reporting.</p>
                    <p><strong>Accounts Opening:</strong> sets starting balances. One line cannot be both debit and credit, to prevent ambiguous opening values.</p>
                    <p><strong>Vouchers (JV/CP/CR/BPV/BRV/CV):</strong> daily accounting entries. Save = draft capture. Post = validation + finalization + audit metadata.</p>
                </div>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">5) Voucher Field Behavior (Deep)</h3>
                <div class="overflow-x-auto border border-slate-300">
                    <table class="w-full border-collapse text-left text-[12px]">
                        <thead>
                            <tr class="bg-[#d8d8d8]">
                                <th class="border border-slate-400 px-2 py-1">Field</th>
                                <th class="border border-slate-400 px-2 py-1">What user enters</th>
                                <th class="border border-slate-400 px-2 py-1">Backend behavior</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="border border-slate-300 px-2 py-1">Voucher Date</td><td class="border border-slate-300 px-2 py-1">Date</td><td class="border border-slate-300 px-2 py-1">Stored in voucher header.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Fiscal Year</td><td class="border border-slate-300 px-2 py-1">Select year</td><td class="border border-slate-300 px-2 py-1">Linked by <span class="font-mono">financial_year_id</span>.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Account ID</td><td class="border border-slate-300 px-2 py-1">Line-level account</td><td class="border border-slate-300 px-2 py-1">Validated against <span class="font-mono">accounts.id</span>.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Debit / Credit</td><td class="border border-slate-300 px-2 py-1">Amounts</td><td class="border border-slate-300 px-2 py-1">Used to compute totals and posting checks.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Amount</td><td class="border border-slate-300 px-2 py-1">Optional amount value</td><td class="border border-slate-300 px-2 py-1">Mapped to debit/credit in cash/bank type vouchers.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Status</td><td class="border border-slate-300 px-2 py-1">Draft (auto), Posted (on approval)</td><td class="border border-slate-300 px-2 py-1">Controls lifecycle and business finalization.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 rounded border border-slate-300 bg-[#fafafa] p-2">
                    <p><strong>Posting logic summary:</strong> voucher cannot post if no lines, unbalanced totals, line/header mismatch, or zero-value invalid totals.</p>
                </div>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">6) Yarn &amp; Grey Transaction Engine</h3>
                <p>
                    All yarn/grey screen forms use common save/post/print logic. Save stores a draft header in
                    <span class="font-mono">inventory_transactions</span> and lines in <span class="font-mono">inventory_transaction_lines</span>.
                </p>
                <ul class="mt-2 list-disc pl-6">
                    <li><strong>Save:</strong> validates date/lines, calculates total qty and amount.</li>
                    <li><strong>Post:</strong> marks transaction as posted (with module + screen route integrity check).</li>
                    <li><strong>Print:</strong> opens printable transaction detail page.</li>
                </ul>
                <p class="mt-2"><strong>Business meaning:</strong> this is operational movement tracking for material flow, and becomes management reporting source.</p>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">7) Reports (Full Working Flow)</h3>
                <div class="rounded border border-slate-300 p-3">
                    <p><strong>Report screens:</strong> Accounts, Yarn, Grey.</p>
                    <p><strong>Filters:</strong> from date / to date are applied to query.</p>
                    <p><strong>Outputs:</strong></p>
                    <ul class="list-disc pl-6">
                        <li><strong>View report:</strong> on-screen table with totals.</li>
                        <li><strong>Export CSV:</strong> download file stream.</li>
                        <li><strong>Print layout:</strong> print-ready HTML page.</li>
                    </ul>
                    <p class="mt-2"><strong>Data source:</strong> vouchers for Accounts report, inventory transactions for Yarn/Grey reports.</p>
                </div>
                <div class="mt-2 rounded border border-slate-300 bg-[#fafafa] p-2">
                    <p><strong>Management use:</strong> use screen view for analysis, CSV export for reconciliation in Excel, print layout for approvals/records.</p>
                </div>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">8) Security and RBAC</h3>
                <p>
                    Every screen/action is permission controlled in UI and backend. If a user does not have action permission, the action is hidden and endpoint
                    also returns forbidden response.
                </p>
                <p class="mt-2">Permission naming follows: <span class="font-mono">module.screen.action</span>.</p>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">9) End-to-end operating flow</h3>
                <ol class="list-decimal pl-6">
                    <li>Set masters (COA, parties, items, godowns, financial year).</li>
                    <li>Create transaction/voucher in draft.</li>
                    <li>Review totals and validations.</li>
                    <li>Post finalized entries.</li>
                    <li>Use Reports to view/export/print.</li>
                </ol>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">10) Function-by-Function quick dictionary</h3>
                <div class="overflow-x-auto border border-slate-300">
                    <table class="w-full border-collapse text-left text-[12px]">
                        <thead><tr class="bg-[#d8d8d8]"><th class="border border-slate-400 px-2 py-1">Function</th><th class="border border-slate-400 px-2 py-1">Meaning in business</th></tr></thead>
                        <tbody>
                            <tr><td class="border border-slate-300 px-2 py-1">Save</td><td class="border border-slate-300 px-2 py-1">Capture entry in draft stage for review/edit before approval.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Post</td><td class="border border-slate-300 px-2 py-1">Finalize transaction after validation checks; considered approved operational/accounting record.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Print</td><td class="border border-slate-300 px-2 py-1">Generate physical-format document for signatures/records.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Export CSV</td><td class="border border-slate-300 px-2 py-1">Send structured report data to Excel or external analysis.</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Dashboard</td><td class="border border-slate-300 px-2 py-1">Entry point per module to open operation-specific screens.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">11) Important URLs</h3>
                <div class="overflow-x-auto border border-slate-300">
                    <table class="w-full border-collapse text-left text-[12px]">
                        <thead><tr class="bg-[#d8d8d8]"><th class="border border-slate-400 px-2 py-1">Section</th><th class="border border-slate-400 px-2 py-1">URL</th></tr></thead>
                        <tbody>
                            <tr><td class="border border-slate-300 px-2 py-1">Main Dashboard</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.accounts.dashboard') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">COA</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.accounts.coa') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Financial Year</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.accounts.financial-year') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Accounts Opening</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.accounts.opening') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">JV Voucher</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.accounts.vouchers.jv') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Yarn Dashboard</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.yarn.dashboard') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Grey Dashboard</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.grey.dashboard') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Reports Dashboard</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.reports.dashboard') }}</td></tr>
                            <tr><td class="border border-slate-300 px-2 py-1">Documentation</td><td class="border border-slate-300 px-2 py-1 font-mono">{{ route('erp.docs') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h3 class="mb-1 text-[14px] font-bold uppercase">12) Practical sample business scenario</h3>
                <div class="rounded border border-slate-300 p-3">
                    <p><strong>Step 1:</strong> Create account <span class="font-mono">1001 - Cash in Hand</span> in COA.</p>
                    <p><strong>Step 2:</strong> Create year <span class="font-mono">2026</span>.</p>
                    <p><strong>Step 3:</strong> Add opening debit 50,000 for cash account.</p>
                    <p><strong>Step 4:</strong> Create JV: expense debit 1,000 and cash credit 1,000.</p>
                    <p><strong>Step 5:</strong> Save JV (draft), review totals, post JV.</p>
                    <p><strong>Step 6:</strong> Open reports and verify totals in Accounts report.</p>
                    <p class="mt-2">This shows complete business path from setup -> transaction -> control -> reporting.</p>
                </div>
            </section>
        </div>
    </div>
@endsection

