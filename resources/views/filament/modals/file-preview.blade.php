<div class="space-y-4">
    @php
        $fileExtension = strtolower(pathinfo($file->file_path, PATHINFO_EXTENSION));
        $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        $isPdf = $fileExtension === 'pdf';
    @endphp

    @if($file->keterangan)
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 mb-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-700 dark:text-gray-200">
                <span class="font-semibold">Keterangan:</span> {{ $file->keterangan }}
            </p>
        </div>
    @endif

    <div class="border border-gray-300 rounded-lg p-4 dark:border-gray-700 bg-white dark:bg-gray-900">
        @if($isImage)
            <img src="{{ route('preview.sp3m-lampiran', $file->lampiran_id) }}" 
                 alt="{{ $file->nama_file }}"
                 class="max-w-full h-auto mx-auto rounded shadow-sm">
        @elseif($isPdf)
            <iframe src="{{ route('preview.sp3m-lampiran', $file->lampiran_id) }}" 
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
                <a href="{{ route('download.sp3m-lampiran', $file->lampiran_id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download File
                </a>
            </div>
        @endif
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-600 dark:text-gray-300">
            <p>Dibuat: {{ $file->created_at->format('d/m/Y H:i') }}</p>
            @if($file->updated_at && $file->updated_at != $file->created_at)
                <p>Diperbarui: {{ $file->updated_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
        <a href="{{ route('download.sp3m-lampiran', $file->lampiran_id) }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 dark:bg-gray-500 border border-gray-600 dark:border-gray-500 rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Download
        </a>
    </div>
</div>
