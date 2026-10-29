<div class="p-6 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Thermal Printer & Hardware Management</h1>
            <p class="text-xs text-slate-500 mt-1">Configure 80mm / 58mm thermal receipt printers, ESC/POS LAN network endpoints, QZ Tray local bridges, and barcode labelers.</p>
        </div>
        <button wire:click="openAddPrinterModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 flex items-center space-x-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Add Printer (Wizard)</span>
        </button>
    </div>

    <!-- Flash Notifications -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center space-x-2 animate-in fade-in shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center space-x-2 animate-in fade-in shadow-xs">
            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex space-x-1 border-b border-slate-200 text-xs font-bold">
        <button wire:click="$set('tab', 'printers')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'printers' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Configured Printers ({{ count($printers) }})
        </button>
        <button wire:click="$set('tab', 'print_jobs')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'print_jobs' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Print Jobs Audit Log
        </button>
        <button wire:click="$set('tab', 'qz_tray_guide')" class="px-4 py-2.5 border-b-2 transition {{ $tab === 'qz_tray_guide' ? 'border-brand-600 text-brand-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            Hardware Bridge & QZ Tray Docs
        </button>
    </div>

    <!-- TAB 1: PRINTER LIST -->
    @if($tab === 'printers')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($printers as $prn)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase
                                    {{ $prn->connection_type === 'lan_escpos' ? 'bg-indigo-50 text-indigo-700' : ($prn->connection_type === 'qz_tray' ? 'bg-amber-50 text-amber-700' : ($prn->connection_type === 'mock' ? 'bg-purple-50 text-purple-700' : 'bg-emerald-50 text-emerald-700')) }}">
                                    {{ str_replace('_', ' ', $prn->connection_type) }}
                                </span>
                                <h3 class="font-bold text-slate-900 text-base mt-2">{{ $prn->name }}</h3>
                                <p class="text-xs text-slate-500">{{ $prn->branch->name }} • {{ $prn->counter->name ?? 'All Counters' }}</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                @if($prn->is_default_receipt)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700" title="Default Receipt Printer">Default Receipt</span>
                                @endif
                                @if($prn->is_default_label)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700" title="Default Barcode Label Printer">Default Label</span>
                                @endif
                            </div>
                        </div>

                        <!-- Technical specs -->
                        <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs text-slate-600">
                            <div>Paper: <strong class="text-slate-800">{{ $prn->paper_width_mm }}mm ({{ $prn->columns }} cols)</strong></div>
                            <div>DPI: <strong class="text-slate-800">{{ $prn->dpi }}</strong></div>
                            <div>Profile: <strong class="text-slate-800">{{ $prn->capability_profile }}</strong></div>
                            <div>Cutter: <strong class="text-slate-800">{{ $prn->has_cutter ? 'Yes' : 'No' }}</strong></div>
                            @if($prn->ip_address)
                                <div class="col-span-2 font-mono text-[11px] text-slate-500">IP: {{ $prn->ip_address }}:{{ $prn->port }}</div>
                            @endif
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <button wire:click="testPrintDiagnostic({{ $prn->id }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-lg transition">
                            Test Print
                        </button>
                        <div class="flex items-center space-x-2">
                            <button wire:click="editPrinter({{ $prn->id }})" class="text-xs text-indigo-600 font-bold hover:underline">
                                Edit
                            </button>
                            <span class="text-slate-300">|</span>
                            <button wire:click="deletePrinter({{ $prn->id }})" onclick="return confirm('Remove printer profile?') || event.stopImmediatePropagation()" class="text-xs text-rose-600 hover:underline">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- TAB 2: PRINT JOBS AUDIT LOG -->
    @if($tab === 'print_jobs')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Job ID</th>
                        <th class="py-3 px-4">Printer</th>
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Reprint?</th>
                        <th class="py-3 px-4">Timestamp</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($printJobs as $job)
                        <tr>
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">#{{ $job->id }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $job->printer->name }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $job->order->invoice_number ?? 'Diagnostic / Label' }}</td>
                            <td class="py-3 px-4 uppercase text-[10px] text-slate-500">{{ $job->job_type }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase
                                    {{ $job->status === 'success' ? 'bg-emerald-50 text-emerald-700' : ($job->status === 'submitted' ? 'bg-indigo-50 text-indigo-700' : ($job->status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $job->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($job->is_reprint)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-100 text-amber-800">REPRINT</span>
                                @else
                                    <span class="text-slate-400">Original</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-400">{{ $job->created_at->format('d/m/y H:i:s') }}</td>
                            <td class="py-3 px-4 text-right">
                                @if($job->order)
                                    <button wire:click="reprintJob({{ $job->id }})" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition">
                                        Reprint (Duplicate)
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">No print jobs logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $printJobs->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 3: QZ TRAY & HARDWARE BRIDGE DOCUMENTATION -->
    @if($tab === 'qz_tray_guide')
        <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm space-y-6 max-w-4xl text-sm leading-relaxed text-slate-700">
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Thermal Printer Connection Architecture</h2>
                <p class="text-xs text-slate-500 mt-1">Understanding local hardware access, raw ESC/POS byte streaming, and browser printing limitations.</p>
            </div>

            <!-- Path Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                        <span class="text-base">🌐</span>
                        <span>1. Browser Thermal Print</span>
                    </div>
                    <p class="text-xs text-slate-600">Zero software setup. Uses CSS <code class="bg-slate-200 px-1 py-0.5 rounded font-mono">@media print</code> with fixed <code class="bg-slate-200 px-1 py-0.5 rounded font-mono">@page { size: 80mm auto }</code>. OS print dialogue opens directly.</p>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                        <span class="text-base">🔌</span>
                        <span>2. QZ Tray USB Bridge</span>
                    </div>
                    <p class="text-xs text-slate-600">Silent, dialogue-free background printing over local USB. Communicates via WebSocket (<code class="bg-slate-200 px-1 py-0.5 rounded font-mono">localhost:8182</code>) with RSA digital signature authorization.</p>
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                        <span class="text-base">📡</span>
                        <span>3. LAN ESC/POS Socket</span>
                    </div>
                    <p class="text-xs text-slate-600">Connects directly to Ethernet/Wi-Fi thermal printers via TCP socket (port 9100). Operates from local shop server or bridge without browser prompts.</p>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-200">
                <h3 class="text-base font-bold text-slate-900">QZ Tray Setup & Troubleshooting Checklist</h3>
                <ol class="list-decimal pl-5 space-y-2 text-xs text-slate-600">
                    <li><strong>Installation:</strong> Install QZ Tray (v2.1+) on the cashier Windows/macOS/Linux terminal from <a href="https://qz.io" target="_blank" class="text-indigo-600 underline">qz.io</a>.</li>
                    <li><strong>Launch at Startup:</strong> Enable "Start at boot" so cashier workstations automatically connect upon terminal power-on.</li>
                    <li><strong>Certificate Trust:</strong> For production silent printing without security warnings, import your shop's digital certificate into QZ Tray, or leave default for manual prompt approval.</li>
                    <li><strong>Port Accessibility:</strong> Ensure firewall allows WebSocket connection on <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">8182</code> (secure) or <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">8181</code> (plain).</li>
                </ol>
            </div>
        </div>
    @endif

    <!-- ADD / EDIT PRINTER WIZARD MODAL -->
    @if($showPrinterModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 border border-slate-200">
                <!-- Wizard Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $editingPrinterId ? 'Edit Thermal Printer' : 'Add Thermal Printer Wizard' }}</h3>
                        <p class="text-xs text-slate-500">Step {{ $wizardStep }} of 3: {{ $wizardStep === 1 ? 'General & Counter Location' : ($wizardStep === 2 ? 'Hardware & Connection' : 'Paper & Capabilities') }}</p>
                    </div>
                    <button wire:click="$set('showPrinterModal', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                <!-- Wizard Steps Progress Bar -->
                <div class="flex items-center space-x-2 my-4">
                    <div class="h-1.5 flex-1 rounded-full {{ $wizardStep >= 1 ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
                    <div class="h-1.5 flex-1 rounded-full {{ $wizardStep >= 2 ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
                    <div class="h-1.5 flex-1 rounded-full {{ $wizardStep >= 3 ? 'bg-indigo-600' : 'bg-slate-200' }}"></div>
                </div>

                <!-- STEP 1: General & Location -->
                @if($wizardStep === 1)
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Friendly Printer Name *</label>
                            <input type="text" wire:model="name" placeholder="e.g. Express Counter 1 - Thermal 80mm" class="w-full px-3 py-2 rounded-xl border-slate-300">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Branch Location</label>
                                <select wire:model="branch_id" class="w-full px-3 py-2 rounded-xl border-slate-300">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Assigned Counter</label>
                                <select wire:model="counter_id" class="w-full px-3 py-2 rounded-xl border-slate-300">
                                    <option value="">-- All Counters / Shared --</option>
                                    @foreach($counters as $cnt)
                                        <option value="{{ $cnt->id }}">{{ $cnt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STEP 2: Connection & Hardware -->
                @if($wizardStep === 2)
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Connection Method *</label>
                            <select wire:model.live="connection_type" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold">
                                <option value="browser">Browser Printing (Default HTML / OS Print dialogue)</option>
                                <option value="qz_tray">QZ Tray Local USB Bridge (Silent printing)</option>
                                <option value="lan_escpos">LAN ESC/POS Direct Socket (Ethernet / Wi-Fi)</option>
                                <option value="mock">Virtual Mock Simulator (Automated tests & demo)</option>
                            </select>
                        </div>

                        @if($connection_type === 'lan_escpos')
                            <div class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <div class="col-span-2">
                                    <label class="block font-bold text-slate-700 mb-1">Printer IP Address</label>
                                    <input type="text" wire:model="ip_address" placeholder="192.168.1.100" class="w-full px-3 py-2 rounded-xl border-slate-300 font-mono">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Port</label>
                                    <input type="number" wire:model="port" value="9100" class="w-full px-3 py-2 rounded-xl border-slate-300 font-mono">
                                </div>
                            </div>
                        @endif

                        @if($connection_type === 'qz_tray')
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <label class="block font-bold text-slate-700 mb-1">OS / CUPS Printer Name</label>
                                <input type="text" wire:model="device_identifier" placeholder="e.g. EPSON TM-T82III Receipt" class="w-full px-3 py-2 rounded-xl border-slate-300 font-mono">
                            </div>
                        @endif

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Capability Profile</label>
                            <select wire:model="capability_profile" class="w-full px-3 py-2 rounded-xl border-slate-300">
                                <option value="tvs_rp3200">TVS RP 3200 LITE (80mm, Partial Cutter, 24V Drawer)</option>
                                <option value="tvs_lp46">TVS LP 46 Dlite (Barcode & Garment Hangtags, 203 DPI)</option>
                                <option value="epson_standard">Epson Standard (TM-T82, TM-T88, TM-m30)</option>
                                <option value="xprinter_80">Xprinter 80mm ESC/POS</option>
                                <option value="xprinter_58">Xprinter / POS-58 Compact</option>
                                <option value="generic_escpos">Generic Standard ESC/POS</option>
                            </select>
                        </div>
                    </div>
                @endif

                <!-- STEP 3: Paper & Capabilities -->
                @if($wizardStep === 3)
                    <div class="space-y-4 text-xs">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Paper Width</label>
                                <select wire:model.live="paper_width_mm" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold">
                                    <option value="104">104 mm (4-inch) TVS LP 46 / 2-Up Roll</option>
                                    <option value="80">80 mm Standard POS Receipt</option>
                                    <option value="58">58 mm Compact POS Receipt</option>
                                    <option value="50">50 mm (2-inch) 1-Up Label Roll</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Char Columns</label>
                                <input type="number" wire:model="columns" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-center">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Resolution (DPI)</label>
                                <input type="number" wire:model="dpi" value="203" class="w-full px-3 py-2 rounded-xl border-slate-300 font-bold text-center">
                            </div>
                        </div>

                        <!-- Hardware checkboxes -->
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" wire:model="has_cutter" class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="font-semibold text-slate-800">Has Automatic Paper Cutter (Feed & Cut)</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" wire:model="has_cash_drawer" class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="font-semibold text-slate-800">Kick Cash Drawer on Cash Sale (Pin 2 Pulse)</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_default_receipt" class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="font-semibold text-slate-800">Set as Default Receipt Printer for this Branch</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_default_label" class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="font-semibold text-slate-800">Set as Default Barcode Label Printer</span>
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Wizard Actions -->
                <div class="mt-6 flex justify-between pt-4 border-t border-slate-100">
                    @if($wizardStep > 1)
                        <button wire:click="prevStep" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">
                            ← Back
                        </button>
                    @else
                        <div></div>
                    @endif

                    @if($wizardStep < 3)
                        <button wire:click="nextStep" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">
                            Next Step →
                        </button>
                    @else
                        <button wire:click="savePrinter" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition">
                            Save Printer Configuration
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

