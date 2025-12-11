<div class="space-y-3">
    @foreach($lampiran as $item)
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer shadow-sm"
             x-data="{ open: false }"
             @click.stop="open = true">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h4 class="font-semibold text-gray-900 dark:text-white">
                            {{ $item->nama_file }}
                        </h4>
                    </div>
                    @if($item->keterangan)
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 ml-7">
                            {{ $item->keterangan }}
                        </p>
                    @endif
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 ml-7">
                        Dibuat: {{ $item->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="ml-4 text-sm text-primary-600 dark:text-primary-400 font-medium">
                    Klik untuk lihat
                </div>
            </div>

            <!-- Modal untuk preview file -->
            <div x-show="open"
                 x-cloak
                 x-transition
                 class="fixed inset-0 z-[100]"
                 style="display: none;"
                 @click.self="open = false">
                <div class="flex items-center justify-center min-h-screen px-4" @click.self="open = false">
                    <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
                    
                    <div class="relative bg-white dark:bg-gray-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-xl z-[101]"
                         @click.stop>
                        <!-- Header -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $item->nama_file }}
                            </h3>
                            <button @click="open = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="p-4 overflow-y-auto max-h-[calc(90vh-8rem)] bg-white dark:bg-gray-900">
                            @php
                                $fileExtension = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION));
                                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                $isPdf = $fileExtension === 'pdf';
                            @endphp

                            @if($isImage)
                                <img src="{{ Storage::url($item->file_path) }}" 
                                     alt="{{ $item->nama_file }}"
                                     class="max-w-full h-auto mx-auto rounded">
                            @elseif($isPdf)
                                <iframe src="{{ Storage::url($item->file_path) }}" 
                                        class="w-full h-[70vh] rounded border border-gray-300 dark:border-gray-700">
                                </iframe>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                                        Preview tidak tersedia untuk tipe file ini
                                    </p>
                                    <a href="{{ Storage::url($item->file_path) }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 shadow-sm">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download File
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            <a href="{{ Storage::url($item->file_path) }}" 
                               download
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 dark:bg-gray-500 border border-gray-600 dark:border-gray-500 rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download
                            </a>
                            <button @click="open = false"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 shadow-sm">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
