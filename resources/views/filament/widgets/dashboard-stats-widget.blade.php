<x-filament-widgets::widget>
    {{-- Loading Indicator --}}
    <div wire:loading class="flex items-center justify-center p-12">
        <div class="text-center space-y-4">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
            <div class="text-lg font-semibold text-gray-700 dark:text-gray-300">Loading...</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Memuat data dashboard...</div>
        </div>
    </div>

    <div class="space-y-6" wire:loading.remove>
        {{-- Year Selector --}}
        <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 ring-1 ring-gray-950/5 dark:ring-white/10">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Overview</h2>
            <div class="flex items-center gap-3">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tahun:</label>
                <select wire:model.live="selectedYear" 
                        class="rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @foreach($this->getYearOptions() as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if(auth()->user()->level->value === 'abk')
            {{-- Dashboard untuk ABK - Tampilan Tabel Sederhana --}}
            @foreach($golonganBbmData as $golonganId => $data)
                <div class="space-y-4">
                    {{-- Section Header --}}
                    <div class="flex items-center gap-3 bg-gradient-to-r from-primary-100 to-primary-200 dark:from-primary-500 dark:to-primary-600 rounded-xl p-6 shadow-lg border border-primary-300 dark:border-primary-700">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary-200 dark:bg-white/20 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🛢️</span>
                        </div>
                        <h2 class="text-2xl font-bold text-primary-900 dark:text-white">
                            BBM {{ $data['golongan'] }}
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- SP3M Card --}}
                        <!-- <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-100 to-blue-200 dark:from-gray-700 dark:to-gray-700 px-6 py-4 border-b border-blue-300 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">📋</span>
                                    <h3 class="text-2xl font-bold text-blue-900 dark:text-white">SP3M</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-gray-900 rounded-lg border border-blue-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $data['sp3m']['bekal'] }}</div>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-blue-50 dark:bg-gray-900 rounded-lg border border-blue-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Qty (Ltr)</div>
                                        <div class="text-2xl font-bold text-blue-700 dark:text-white">{{ number_format($data['sp3m']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        {{-- Pengambilan/DO Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                            <div class="bg-gradient-to-r from-sky-100 to-sky-200 dark:from-gray-700 dark:to-gray-700 px-6 py-4 border-b border-sky-300 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">📦</span>
                                    <h3 class="text-2xl font-bold text-sky-900 dark:text-white">Pengambilan/DO</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $data['pengambilan']['bekal'] }}</div>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Qty (Ltr)</div>
                                        <div class="text-2xl font-bold text-sky-700 dark:text-white">{{ number_format($data['pengambilan']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-4 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Sisa SP3M (Ltr)</div>
                                        <div class="text-2xl font-bold text-amber-700 dark:text-white">{{ number_format($data['pengambilan']['sisa_sp3m'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pemakaian Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-100 to-orange-200 dark:from-gray-700 dark:to-gray-700 px-6 py-4 border-b border-orange-300 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">⛽</span>
                                    <h3 class="text-2xl font-bold text-orange-900 dark:text-white">Pemakaian</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-gray-900 rounded-lg border border-orange-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $data['pemakaian']['bekal'] }}</div>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-orange-50 dark:bg-gray-900 rounded-lg border border-orange-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Pakai (Ltr)</div>
                                        <div class="text-2xl font-bold text-orange-700 dark:text-white">{{ number_format($data['pemakaian']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-4 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Pengisian (Ltr)</div>
                                        <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ number_format($data['pemakaian']['pengisian'] ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @elseif (auth()->user()->level->value === 'kansar')
            {{-- Dashboard untuk ABK - Tampilan Tabel Sederhana --}}
            @foreach($golonganBbmData as $golonganId => $data)
                <div class="space-y-4">
                    {{-- Section Header --}}
                    <div class="flex items-center gap-3 bg-gradient-to-r from-primary-100 to-primary-200 dark:from-primary-500 dark:to-primary-600 rounded-xl p-6 shadow-lg border border-primary-300 dark:border-primary-700">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary-200 dark:bg-white/20 rounded-lg flex items-center justify-center">
                            <span class="text-2xl">🛢️</span>
                        </div>
                        <h2 class="text-2xl font-bold text-primary-900 dark:text-white">
                            BBM {{ $data['golongan'] }}
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- SP3M Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-100 to-blue-200 dark:from-gray-700 dark:to-gray-700 px-6 py-4 border-b border-blue-300 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">📋</span>
                                    <h3 class="text-2xl font-bold text-blue-900 dark:text-white">SP3M</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-gray-900 rounded-lg border border-blue-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $data['sp3m']['bekal'] }}</div>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Qty (Ltr)</div>
                                        <div class="text-2xl font-bold text-blue-700 dark:text-white">{{ number_format($data['sp3m']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pengambilan/DO Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                            <div class="bg-gradient-to-r from-sky-100 to-sky-200 dark:from-gray-700 dark:to-gray-700 px-6 py-4 border-b border-sky-300 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">📦</span>
                                    <h3 class="text-2xl font-bold text-sky-900 dark:text-white">Pengambilan/DO</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $data['pengambilan']['bekal'] }}</div>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Qty (Ltr)</div>
                                        <div class="text-2xl font-bold text-sky-700 dark:text-white">{{ number_format($data['pengambilan']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-4 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Sisa SP3M (Ltr)</div>
                                        <div class="text-2xl font-bold text-amber-700 dark:text-white">{{ number_format($data['pengambilan']['sisa_sp3m'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pemakaian Card --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-100 to-orange-200 dark:from-gray-700 dark:to-gray-700 px-6 py-4 border-b border-orange-300 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">⛽</span>
                                    <h3 class="text-2xl font-bold text-orange-900 dark:text-white">Pemakaian</h3>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-gray-900 rounded-lg border border-orange-200 dark:border-gray-700">
                                        <div>
                                            <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $data['pemakaian']['bekal'] }}</div>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-orange-50 dark:bg-gray-900 rounded-lg border border-orange-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Pakai (Ltr)</div>
                                        <div class="text-2xl font-bold text-orange-700 dark:text-white">{{ number_format($data['pemakaian']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-4 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Pengisian (Ltr)</div>
                                        <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ number_format($data['pemakaian']['pengisian'] ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            {{-- Dashboard untuk Admin, Kanpus, Kansar - Tampilan Card Detail --}}
            {{-- Loop through each Golongan BBM --}}
        @foreach($golonganBbmData as $golonganId => $data)
            <div class="space-y-4">
                {{-- Section Header with Badge --}}
                <div class="flex items-center gap-3 bg-gradient-to-r from-primary-100 to-primary-200 dark:from-primary-500 dark:to-primary-600 rounded-xl p-6 shadow-lg border border-primary-300 dark:border-primary-700">
                    <div class="flex-shrink-0 w-12 h-12 bg-primary-200 dark:bg-white/20 rounded-lg flex items-center justify-center">
                        <span class="text-2xl">🛢️</span>
                    </div>
                    <h2 class="text-2xl font-bold text-primary-900 dark:text-white">
                        BBM {{ $data['golongan'] }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Pagu Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="bg-gradient-to-r from-cyan-100 to-cyan-200 dark:from-cyan-500 dark:to-cyan-600 px-6 py-4 border-b border-cyan-300 dark:border-cyan-700">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">💰</span>
                                <h3 class="text-xl font-bold text-cyan-900 dark:text-white">Pagu (Rp)</h3>
                            </div>
                        </div>
                        <div class="p-6 space-y-3">
                            @if(auth()->user()->level->value === 'admin' || auth()->user()->level->value === 'kanpus')
                            <div class="flex justify-between items-center p-3 bg-cyan-50 dark:bg-gray-900 rounded-lg border border-cyan-200 dark:border-gray-700">
                                <span class="font-semibold text-gray-900 dark:text-amber-500">Total Pagu</span>
                                <span class="font-bold text-cyan-700 dark:text-white">{{ number_format($data['pagu']['total'], 0, ',', '.') }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-gray-100 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="text-md font-bold text-gray-600 dark:!text-cyan-300 mb-1">TW 1</div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ number_format($data['pagu']['tw1'], 0, ',', '.') }}</div>
                                </div>
                                <div class="p-3 bg-gray-100 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="text-md font-bold text-gray-600 dark:!text-cyan-300 mb-1">TW 2</div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ number_format($data['pagu']['tw2'], 0, ',', '.') }}</div>
                                </div>
                                <div class="p-3 bg-gray-100 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="text-md font-bold text-gray-600 dark:!text-cyan-300 mb-1">TW 3</div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ number_format($data['pagu']['tw3'], 0, ',', '.') }}</div>
                                </div>
                                <div class="p-3 bg-gray-100 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="text-md font-bold text-gray-600 dark:!text-cyan-300 mb-1">TW 4</div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ number_format($data['pagu']['tw4'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @endif
                            @if(auth()->user()->level->value === 'admin' || auth()->user()->level->value === 'kanpus')
                            <div class="flex justify-between items-center p-3 bg-green-50 dark:bg-gray-900 rounded-lg border-2 border-green-400 dark:border-green-600">
                                <span class="font-bold text-gray-900 dark:text-green-400">Sisa</span>
                                <span class="font-bold text-green-700 dark:text-green-400">{{ number_format($data['pagu']['total'] - $data['sp3m']['jumlah_harga'], 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- SP3M/SPT Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="bg-gradient-to-r from-blue-100 to-blue-200 dark:from-blue-500 dark:to-blue-600 px-6 py-4 border-b border-blue-300 dark:border-blue-700">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">📋</span>
                                <h3 class="text-xl font-bold text-blue-900 dark:text-white">SP3M</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-gray-900 rounded-lg border border-blue-200 dark:border-gray-700">
                                    <div>
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $data['sp3m']['bekal'] }}</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-blue-50 dark:bg-gray-900 rounded-lg border border-blue-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Qty (Ltr)</div>
                                        <div class="text-2xl font-bold text-blue-700 dark:text-white">{{ number_format($data['sp3m']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    @if(auth()->user()->level->value === 'admin' || auth()->user()->level->value === 'kanpus')
                                    <div class="p-3 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Jml Harga (Rp)</div>
                                        <div class="text-lg font-bold text-blue-700 dark:text-white">{{ number_format($data['sp3m']['jumlah_harga'], 0, ',', '.') }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pengambilan/DO/Nota Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="bg-gradient-to-r from-sky-100 to-sky-200 dark:from-sky-500 dark:to-sky-600 px-6 py-4 border-b border-sky-300 dark:border-sky-700">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">📦</span>
                                <h3 class="text-xl font-bold text-sky-900 dark:text-white">Pengambilan/DO/Nota</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                    <div>
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $data['pengambilan']['bekal'] }}</div>
                                    </div>
                                </div>
                                @if(auth()->user()->level->value === 'admin' || auth()->user()->level->value === 'kanpus')
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="p-3 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Qty (Ltr)</div>
                                        <div class="text-lg font-bold text-sky-700 dark:text-white">{{ number_format($data['pengambilan']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-3 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Jml Harga</div>
                                        <div class="text-lg font-bold text-sky-700 dark:text-white">{{ number_format($data['pengambilan']['jumlah_harga'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-3 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Sisa SP3M</div>
                                        <div class="text-lg font-bold text-amber-700 dark:text-white">{{ number_format($data['pengambilan']['sisa_sp3m'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                @else
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="p-3 bg-sky-50 dark:bg-gray-900 rounded-lg border border-sky-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Qty (Ltr)</div>
                                        <div class="text-lg font-bold text-sky-700 dark:text-white">{{ number_format($data['pengambilan']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-3 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Sisa SP3M</div>
                                        <div class="text-lg font-bold text-amber-700 dark:text-white">{{ number_format($data['pengambilan']['sisa_sp3m'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Pemakaian Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="bg-gradient-to-r from-orange-100 to-orange-200 dark:from-orange-500 dark:to-orange-600 px-6 py-4 border-b border-orange-300 dark:border-orange-700">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">⛽</span>
                                <h3 class="text-xl font-bold text-orange-900 dark:text-white">Pemakaian</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-gray-900 rounded-lg border border-orange-200 dark:border-gray-700">
                                    <div>
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-1">Item Bekal</div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $data['pemakaian']['bekal'] }}</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="p-4 bg-orange-50 dark:bg-gray-900 rounded-lg border border-orange-200 dark:border-gray-700">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Pakai (Ltr)</div>
                                        <div class="text-2xl font-bold text-orange-700 dark:text-white">{{ number_format($data['pemakaian']['qty'], 0, ',', '.') }}</div>
                                    </div>
                                    <div class="p-3 bg-amber-50 dark:bg-gray-900 rounded-lg border-2 border-amber-500 dark:border-amber-600">
                                        <div class="text-md font-medium text-gray-600 dark:text-amber-500 mb-2">Pengisian (Ltr)</div>
                                        <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ number_format($data['pemakaian']['pengisian'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</x-filament-widgets::widget>
