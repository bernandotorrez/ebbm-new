<div class="overflow-x-auto">
    @if($lampiran->count() > 0)
        <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 border-b dark:border-gray-700">
                <tr>
                    <th scope="col" class="px-4 py-3 text-gray-700 dark:text-white">No</th>
                    <th scope="col" class="px-4 py-3 text-gray-700 dark:text-white">Nama File</th>
                    <th scope="col" class="px-4 py-3 text-gray-700 dark:text-white">Keterangan</th>
                    <th scope="col" class="px-4 py-3 text-gray-700 dark:text-white">Tanggal</th>
                    <th scope="col" class="px-4 py-3 text-center text-gray-700 dark:text-white">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lampiran as $index => $item)
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">
                            {{ $index + 1 }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $item->nama_file }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-white">
                            {{ $item->keterangan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-white">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                @php
                                    $fileExtension = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION));
                                    $isPreviewable = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf']);
                                @endphp
                                
                                @if($isPreviewable)
                                    <a href="{{ route('preview.sp3m-lampiran', $item->lampiran_id) }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat
                                    </a>
                                @endif
                                
                                <a href="{{ route('download.sp3m-lampiran', $item->lampiran_id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gray-600 dark:bg-gray-500 border border-gray-600 dark:border-gray-500 rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 transition shadow-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-12 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Tidak ada lampiran</p>
        </div>
    @endif
</div>
