<x-filament-panels::page>
    @php
        $kantorSarId = $this->data['kantor_sar_id'] ?? $this->kantor_sar_id;
        $tahun = $this->data['tahun'] ?? $this->tahun;
    @endphp
    
    <style>
        .rekap-table {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-radius: 8px;
            overflow: hidden;
        }
        .rekap-table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 700;
            font-size: 15px;
            text-align: center;
            padding: 16px 24px;
            letter-spacing: 0.05em;
        }
        .rekap-table tbody td {
            padding: 14px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .rekap-table tbody tr:hover {
            background-color: #f9fafb;
        }
        .rekap-table .total-row {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            font-weight: 700;
            font-size: 15px;
        }
        .rekap-table .total-row:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        }
        .export-btn {
            background: #10b981;
            border: none;
            box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.39);
            transition: all 0.3s ease;
        }
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
    </style>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Filter Laporan
                    </h3>
                    @if($kantorSarId && $tahun)
                        <button 
                            wire:click="exportToExcel" 
                            class="export-btn inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm text-white uppercase tracking-wider transition-all duration-300"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export to Excel
                        </button>
                    @endif
                </div>
                {{ $this->form }}
            </div>
        </div>
        
        @if($kantorSarId && $tahun)
            @php
                $rekapData = $this->getRekapData();
                $kantorSar = \App\Models\KantorSar::find($kantorSarId);
                $kantorSarName = $kantorSar ? $kantorSar->kantor_sar : 'Semua Kantor SAR';
            @endphp
            
            <!-- Report Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                            REKAP TAGIHAN PENGGUNAAN BBM
                        </h1>
                        <p class="text-xl font-semibold text-gray-700 dark:text-gray-300 mt-2">
                            Kantor SAR: {{ $kantorSarName }}
                        </p>
                        <p class="text-xl font-semibold text-gray-700 dark:text-gray-300">
                            Tahun: {{ $tahun }}
                        </p>
                    </div>
                    
                    <!-- Data Table -->
                    <div class="overflow-x-auto">
                        <table class="rekap-table min-w-full">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>PERIODE</th>
                                    <th>JMLAH BBM (Liter)</th>
                                    <th>JUMLAH PEMBAYARAN (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapData['data'] as $index => $item)
                                    <tr class="{{ $index % 2 == 0 ? '' : 'bg-gray-50' }}">
                                        <td class="text-center font-medium">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="text-center font-medium">
                                            {{ $item['month'] }}
                                        </td>
                                        <td class="text-right font-medium">
                                            {{ number_format($item['total_bbm'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-right font-medium">
                                            Rp {{ number_format($item['total_pembayaran'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <!-- Total Row -->
                                <tr class="total-row">
                                    <td class="text-center"></td>
                                    <td class="text-center">JUMLAH</td>
                                    <td class="text-right">
                                        {{ number_format($rekapData['total_bbm'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        Rp {{ number_format($rekapData['total_pembayaran'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Pilih Filter untuk Melihat Data
                        </h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            Silakan pilih Kantor SAR dan Tahun untuk menampilkan rekap data Delivery Order.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>