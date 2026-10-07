@extends('layouts.admin')

@section('page_title', 'Database Backups & Recovery')

@section('content')
<div class="space-y-6" x-data="{ restoreModalOpen: false, targetFile: '', confirmInput: '' }">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Database Safety Backup & Recovery</h2>
            <p class="text-sm text-gray-500">Create point-in-time SQLite backups and execute safe rollbacks with automatic safety snapshots.</p>
        </div>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition flex items-center gap-2">
                <span>💾</span> Create Backup Now
            </button>
        </form>
    </div>

    {{-- Info Card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-900 leading-relaxed flex items-start gap-3">
        <span class="text-base">ℹ️</span>
        <div>
            <strong class="font-bold">Safety Recovery Protocol:</strong>
            Whenever you restore a database backup, the system automatically creates an instant <strong>Pre-Restore Snapshot</strong> of your live database before applying the restore. This guarantees you can immediately revert back if needed.
        </div>
    </div>

    {{-- Backups List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Backup File</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">File Size</th>
                        <th class="px-6 py-3">Created At</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($files as $file)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3.5 font-mono text-xs font-bold text-gray-800">
                                {{ $file['filename'] }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($file['is_snapshot'])
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Safety Snapshot
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        Manual Backup
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-500">
                                {{ $file['size_formatted'] }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-gray-400">
                                {{ $file['created_at']->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.backups.download', $file['filename']) }}" 
                                   class="text-xs text-blue-600 hover:underline font-semibold">Download</a>

                                <button type="button"
                                        @click="targetFile = '{{ $file['filename'] }}'; confirmInput = ''; restoreModalOpen = true"
                                        class="text-xs text-amber-600 hover:underline font-semibold">
                                    Restore
                                </button>

                                <form method="POST" action="{{ route('admin.backups.destroy', $file['filename']) }}" class="inline"
                                      onsubmit="return confirm('Delete backup {{ $file['filename'] }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No database backups created yet. Click "Create Backup Now" above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Restore Confirmation Modal --}}
    <div x-show="restoreModalOpen" x-cloak
         class="fixed inset-y-0 inset-x-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4"
             @click.away="restoreModalOpen = false">
            
            <div class="flex items-center gap-3 text-red-600">
                <span class="text-2xl">⚠️</span>
                <h3 class="font-bold text-lg text-gray-900">Confirm Database Restore</h3>
            </div>

            <p class="text-xs text-gray-600 leading-relaxed">
                You are about to restore the database from:
                <span class="font-mono font-bold block text-gray-900 bg-gray-100 p-2 rounded mt-1 text-[11px]" x-text="targetFile"></span>
            </p>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900">
                A pre-restore snapshot of the current database will be saved before replacement.
            </div>

            <form :action="'{{ url('admin/backups') }}/' + encodeURIComponent(targetFile) + '/restore'" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Type <span class="font-mono text-red-600 font-bold">RESTORE</span> in capital letters to confirm:
                    </label>
                    <input type="text" name="confirmation" x-model="confirmInput" required
                           placeholder="Type RESTORE here"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="restoreModalOpen = false" class="px-4 py-2 border rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" :disabled="confirmInput !== 'RESTORE'"
                            :class="confirmInput === 'RESTORE' ? 'bg-red-600 hover:bg-red-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                            class="px-4 py-2 text-white rounded-lg text-xs font-bold transition">
                        Confirm & Restore
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

