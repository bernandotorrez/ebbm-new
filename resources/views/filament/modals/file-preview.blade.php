<div class="space-y-4">
    @php
        $fileExtension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
        $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        $isPdf = $fileExtension === 'pdf';
    @endphp

    @if($file->keterangan)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-4">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                <span class="font-semibold">Keterangan:</span> {{ $file->keterangan }}
            </p>
        </div>
    @endif

    <div class="border rounded-lg p-4 dark:border-gray-700">
        @if($isImage)
            <img src="{{ Storage::url($file->file_path) }}" 
                 alt="{{ $file->nama_file }}"
                 class="max-w-full h-auto mx-auto rounded">
        @elseif($isPdf)
            <iframe src="{{ Storage::url($file->file_path) }}" 
                    class="w-full h-[70vh] rounded border dark:border-gray-700">
            </iframe>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Preview tidak tersedia untuk tipe file ini
                </p>
                <a href="{{ Storage::url($file->file_path) }}" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download File
                </a>
            </div>
        @endif
    </div>

    <div class="flex items-center justify-between pt-4 border-t dark:border-gray-700">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            <p>Dibuat: {{ $file->created_at->format('d/m/Y H:i') }}</p>
            @if($file->updated_at && $file->updated_at != $file->created_at)
                <p>Diperbarui: {{ $file->updated_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
        <a href="{{ Storage::url($file->file_path) }}" 
           download
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download
        </a>
    </div>
</div>
