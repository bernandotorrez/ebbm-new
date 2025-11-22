<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::card>
            <form wire:submit.prevent="tampilkan">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{ $this->form }}
                </div>
                
                <div class="flex gap-3 mt-6">
                    <x-filament::button type="submit" color="primary">
                        Tampilkan
                    </x-filament::button>
                    
                    @if($showTable)
                        <x-filament::button 
                            type="button" 
                            color="success"
                            wire:click="exportExcel">
                            Excel
                        </x-filament::button>
                    @endif
                </div>
            </form>
        </x-filament::card>

        {{-- Report Title --}}
        @if($showTable)
            <div class="text-center">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    LAPORAN DETIL TAGIHAN PENGGUNAAN BBM ALUT 
                    @if($kantor_sar_id && $kantor_sar_id !== 'semua')
                        ({{ \App\Models\KantorSar::find($kantor_sar_id)?->kantor_sar ?? 'KANTOR SAR' }})
                    @else
                        (SEMUA KANTOR SAR)
                    @endif
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    PERIODE (TGL AWAL) {{ \Carbon\Carbon::parse($tanggal_awal)->format('d-m-Y') }} s.d PERIODE (TGL AKHIR) {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d-m-Y') }}
                </p>
            </div>

            {{-- Table --}}
            <x-filament::card>
                {{ $this->table }}
            </x-filament::card>
        @endif
    </div>
</x-filament-panels::page>
