<x-filament-panels::page>
    @php
        $kantorSarId = $this->data['kantor_sar_id'] ?? $this->kantor_sar_id;
        $tahun = $this->data['tahun'] ?? $this->tahun;
    @endphp
    
    <style>
        .rekap-table {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .rekap-table thead th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .rekap-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .rekap-table tbody tr:hover {
            background-color: #f9fafb;
        }
        .rekap-table .total-row {
            background-color: #f3f4f6;
            font-weight: 600;
            border-top: 2px solid #e5e7eb;
        }
        .rekap-table .total-row:hover {
            background-color: #f3f4f6 !important;
        }
        .dark .rekap-table {
            border-color: #374151;
        }
        .dark .rekap-table thead th {
            background-color: #374151;
            color: #f9fafb;
            border-bottom-color: #4b5563;
        }
        .dark .rekap-table tbody td {
            border-bottom-color: #374151;
        }
        .dark .rekap-table .total-row {
            background-color: #374151;
            border-top-color: #4b5563;
        }
        .dark .rekap-table .total-row:hover {
            background-color: #374151 !important;
        }
    </style>
    <div class="space-y-6">
        <!-- Filter Form -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                {{ $this->form }}
                
                @if($kantorSarId && $tahun)
                    <div class="mt-4 pt-4">
                        <x-filament-panels::form.actions
                            :actions="$this->getFormActions()"
                            :full-width="$this->hasFullWidthFormActions()"
                        />
                    </div>
                @endif
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